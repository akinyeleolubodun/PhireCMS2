<?php
/**
 * Pop PHP Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.TXT.
 * It is also available through the world-wide-web at this URL:
 * http://www.popphp.org/LICENSE.TXT
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@popphp.org so we can send you a copy immediately.
 *
 * @category   Pop
 * @package    Pop_Font
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Font\TrueType\Table;

/**
 * This is the Glyf class for the Font component.
 *
 * @category   Pop
 * @package    Pop_Font
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 * @version    1.0.2
 */
class Glyf
{

    /**
     * Glyphs
     * @var array
     */
    public $glyphs = array();

    /**
     * Glyph widths
     * @var array
     */
    public $glyphWidths = array();

    /**
     * Constructor
     *
     * Instantiate a TTF 'glyf' table object.
     *
     * @param  Pop_Font $font
     * @return void
     */
    public function __construct($font)
    {
        $locaLength = count($font->tables['loca']->offsets);
        $j = 0;
        foreach ($font->tables['loca']->offsets as $offset) {
            $bytePos = $font->tableInfo['glyf']->offset + $offset;
            $ary = unpack(
                'nnumberOfContours/' .
                'nxMin/' .
                'nyMin/' .
                'nxMax/' .
                'nyMax', $font->read($bytePos, 10)
            );
            $ary = $font->shiftToSigned($ary);
            $ary['xMin'] = $font->toEmSpace($ary['xMin']);
            $ary['yMin'] = $font->toEmSpace($ary['yMin']);
            $ary['xMax'] = $font->toEmSpace($ary['xMax']);
            $ary['yMax'] = $font->toEmSpace($ary['yMax']);
            $ary['width'] = $ary['xMin'] + $ary['xMax'];
            $this->glyphWidths[] = $ary['width'];

            $bytePos += 10;
            $ary['endPtsOfContours'] = array();
            $ary['instructionLength'] = null;
            $ary['instructions'] = null;
            $ary['flags'] = null;

            // The simple and composite glyph descriptions may not be necessary.
            // If simple glyph.
            if ($ary['numberOfContours'] > 0) {
                for ($i = 0; $i < $ary['numberOfContours']; $i++) {
                    $ar = unpack('nendPt', $font->read($bytePos, 2));
                    $ary['endPtsOfContours'][$i] = $ar['endPt'];
                    $bytePos += 2;
                }
                $ar = unpack('ninstructionLength', $font->read($bytePos, 2));
                $ary['instructionLength'] = $ar['instructionLength'];
                $bytePos += 2;
                if ($ary['instructionLength'] > 0) {
                    for ($i = 0; $i < $ary['instructionLength']; $i++) {
                        $byte = $font->read($bytePos, 1);
                        if (strlen($byte) != 0) {
                            $ar = unpack('Cinstruction', $byte);
                            $ary['instructions'][$i] = $ar['instruction'];
                            $bytePos++;
                        } else {
                            $ary['instructions'][$i] = null;
                        }
                    }
                }
                $bytePos++;
                $byte = $font->read($bytePos, 1);
                if (strlen($byte) != 0) {
                    $ar = unpack('Cflags', $byte);
                    $ary['flags'] = $ar['flags'];
                } else {
                    $ary['flags'] = 0;
                }
                if ($j < ($locaLength - 1)) {
                    $this->glyphs[] = $ary;
                }
            // Stopped here. Still need to get the x & y coordinates of the simple glyph.
            // Else, if composite glyph.
            } else {
                if ($j < ($locaLength - 1)) {
                    // Composite glyph goes here.
                }
            }
            $j++;
        }
    }

}
