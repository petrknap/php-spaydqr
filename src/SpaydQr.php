<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr;

use DateTimeInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Money\Money;
use Sunfox\Spayd\Spayd;

final class SpaydQr
{
    public const QR_SIZE = 300;
    public const QR_MARGIN = 0;

    private function __construct(
        public readonly SpaydBuilder $spayd,
        private readonly BuilderInterface $qrCodeBuilder,
    ) {
    }

    /**
     * @see https://qr-platba.cz/pro-vyvojare/specifikace-formatu/ Atributy, které jsou schopny zpracovat všechny banky v ČR pro tuzemský platební styk
     */
    public static function create(
        string $iban,
        Money $amount,
        DateTimeInterface $dueDate = null,
        string $message = null,
        int $variableSymbol = null,
        int $specificSymbol = null,
        int $constantSymbol = null,
        QrCodeWriter $writer = QrCodeWriter::Png
    ): self {
        $spayd = SpaydBuilder::create()
            ->add(SpaydKey::Iban, $iban)
            ->addAmount($amount);

        if ($dueDate !== null) {
            $spayd->add(SpaydKey::DueDate, $dueDate);
        }

        if ($message !== null) {
            $spayd->add(SpaydKey::Message, $message);
        }

        if ($variableSymbol !== null) {
            $spayd->add(SpaydKey::VariableSymbol, $variableSymbol);
        }

        if ($specificSymbol !== null) {
            $spayd->add(SpaydKey::SpecificSymbol, $specificSymbol);
        }

        if ($constantSymbol !== null) {
            $spayd->add(SpaydKey::ConstantSymbol, $constantSymbol);
        }

        return new self(
            $spayd,
            Builder::create()
                ->writer($writer->endroid())
                ->encoding(new Encoding('UTF-8')),
        );
    }

    /**
     * @internal for testing purposes only
     */
    public static function testable(?Spayd $spayd = null, ?BuilderInterface $qrCodeBuilder = null): self
    {
        return new self(
            SpaydBuilder::testable($spayd),
            $qrCodeBuilder ?? Builder::create(),
        );
    }

    # region QR code
    public function setWriter(QrCodeWriter $writer): self
    {
        $this->qrCodeBuilder
            ->writer($writer->endroid());
        return $this;
    }

    public function getContentType(): string
    {
        return $this->buildQrCode(null, null)->getMimeType();
    }

    public function getContent(int $size = self::QR_SIZE, int $margin = self::QR_MARGIN): string
    {
        return $this->buildQrCode($size, $margin)->getString();
    }

    public function getDataUri(int $size = self::QR_SIZE, int $margin = self::QR_MARGIN): string
    {
        return $this->buildQrCode($size, $margin)->getDataUri();
    }

    public function writeFile(string $path, int $size = self::QR_SIZE, int $margin = self::QR_MARGIN): void
    {
        $this->buildQrCode($size, $margin)->saveToFile($path);
    }

    private function buildQrCode(?int $size, ?int $margin): ResultInterface
    {
        $this->qrCodeBuilder->data($this->spayd->build());

        if ($size !== null) {
            $this->qrCodeBuilder->size($size);
        }

        if ($margin !== null) {
            $this->qrCodeBuilder->margin($margin);
        }

        return $this->qrCodeBuilder->build();
    }
    # endregion

    # region SPayD - commonly supported optional values <https://qr-platba.cz/pro-vyvojare/specifikace-formatu/> (Tabulka 3)
    public function setConstantSymbol(int $constantSymbol): self
    {
        $this->spayd
            ->remove(SpaydKey::ConstantSymbol)
            ->add(SpaydKey::ConstantSymbol, $constantSymbol)
        ;
        return $this;
    }

    public function setDueDate(DateTimeInterface $dueDate): self
    {
        $this->spayd
            ->remove(SpaydKey::DueDate)
            ->add(SpaydKey::DueDate, $dueDate)
        ;
        return $this;
    }

    public function setMessage(string $message): self
    {
        $this->spayd
            ->remove(SpaydKey::Message)
            ->add(SpaydKey::Message, $message)
        ;
        return $this;
    }

    public function setSpecificSymbol(int $specificSymbol): self
    {
        $this->spayd
            ->remove(SpaydKey::SpecificSymbol)
            ->add(SpaydKey::SpecificSymbol, $specificSymbol)
        ;
        return $this;
    }

    public function setVariableSymbol(int $variableSymbol): self
    {
        $this->spayd
            ->remove(SpaydKey::VariableSymbol)
            ->add(SpaydKey::VariableSymbol, $variableSymbol)
        ;
        return $this;
    }
    # endregion
}
