<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr\Exception;

use PetrKnap\Shorts\Exception\CouldNotProcessData;

/**
 * @extends CouldNotProcessData<mixed>
 */
final class CouldNotConvertValue extends CouldNotProcessData implements SpaydValueException
{
}
