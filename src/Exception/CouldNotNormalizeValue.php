<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr\Exception;

use PetrKnap\Shorts\Exception\CouldNotProcessData;

final class CouldNotNormalizeValue extends CouldNotProcessData implements SpaydValueException
{
}
