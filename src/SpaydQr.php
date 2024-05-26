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

    public static function create(
        string $iban,
        Money $money,
        ?DateTimeInterface $dueDate = null,
        ?string $message = null,
        ?int $variableSymbol = null,
        ?int $specificSymbol = null,
        ?int $constantSymbol = null,
        QrCodeWriter $writer = QrCodeWriter::Png
    ): self {
        $spayd = SpaydBuilder::create()
            ->add(SpaydKey::Iban, $iban)
            ->add(SpaydKey::Amount, $money)
            ->add(SpaydKey::CurrencyCode, $money);

        if ($dueDate !== null) {
            $spayd->add(SpaydKEy::DueDate, $dueDate);
        }

        if ($message !== null) {
            $spayd->add(SpaydKey::Message, $message);
        }

        if ($variableSymbol !== null) {
            $spayd->add(SpaydKey::VariableSymbol, $variableSymbol);
        }

        if ($specificSymbol !== null) {
            $spayd->add(SpaydKey::VariableSymbol, $specificSymbol);
        }

        if ($constantSymbol !== null) {
            $spayd->add(SpaydKey::VariableSymbol, $constantSymbol);
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
}
