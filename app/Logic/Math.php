<?php
/**
 *
 * This file is property of XANDO BVBA
 *
 * ALL RIGHTS RESERVED
 *
 * You are NOT allowed to view, use, reproduce, copy, change, distribute,
 * publish, resell, demonstrate or do anything else with this source code
 * unless you have the explicit written permission of XANDO BVBA to do so.
 *
 * (c) Sébastien Jacobs <sebastien@xando.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Logic;


class Math {
    /**
     * @param $float float
     * @return string
     */
    public static function round2Decimals($float)
    {
        return number_format((float) $float, 2, '.', '');
    }
}