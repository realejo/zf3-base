<?php
/**
 * @see https://gist.github.com/mcaskill/02636e5970be1bb22270#file-function-date-format-conversion-php
 */

namespace Realejo\View\Helper;

use Zend\View\Helper\AbstractHelper;

class FormatFileSize extends AbstractHelper
{
    public function __invoke(int $bytes, int $precision = 2):string
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);
        // $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
