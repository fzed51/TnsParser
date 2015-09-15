<?php

/*
 * The MIT License
 *
 * Copyright 2015 fabien.sanchez fzed51@gmail.com.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace fzed51\TnsParser;

/**
 * Description of TnsParser
 *
 * @author fabien.sanchez
 */
class TnsParser {

    private $strTnsName;

    function __construct() {

    }

    function setTnsName($string) {
        $this->strTnsName = self::cleanTnsName($string);
    }

    function setTnsNameFromFile($filename) {
        if (file_exists($filename)) {
            $this->setTnsName(file_get_contents($fileName));
        } else {
            throw new InvalidArgumentException("'$fileName' n'est pas un fichier valide.");
        }
    }

    static public function parseString($strTnsName) {
        $p = new self();
        $p->setTnsName($strTnsName);
        return $p->parse();
    }

    static public function parseFile($fileName) {
        $p = new self();
        $p->setTnsNameFromFile($fileName);
        return $p->parse();
    }

    static public function cleanTnsName($strTnsName) {
// netoie les commentaires
        $strTnsName = preg_replace("/#.*$/m", '', $strTnsName);

// netoie les espaces et les saut de ligne
        $strTnsName = preg_replace("/\s*\(\s*/", '(', $strTnsName);
        $strTnsName = preg_replace("/\s*\)/", ')', $strTnsName);
        $strTnsName = preg_replace("/[ \t]+/", ' ', $strTnsName);
        $strTnsName = preg_replace("/(\r?\n)+/", "\n", $strTnsName);
        $strTnsName = trim($strTnsName);

        return $strTnsName;
    }

    function parse() {
        $lignes = explode("\n", $this->strTnsName);
        $tns = [];

        foreach ($lignes as $ligne) {
            $netServiceName = self::readKey($ligne);
            self::asValue($ligne);
            $description = self::readValue($ligne);
            $netServiceNames = explode(',', $netServiceName);
            foreach ($netServiceNames as $serviceName) {
                $tns[$serviceName] = $description;
            }
        }

        return $tns;
    }

    static private function readCar(&$string, $unshift = true) {
        if (strlen($string) > 0) {
            $car = $string[0];
            if ($unshift) {
                $string = substr($string, 1);
            }
            return $car;
        } else {
            return '';
        }
    }

    static private function skipCar(&$string, $cars = ' ') {
        $pattern = "/[$cars]/";
        $continue = true;
        do {
            $car = self::readcar($string);
            if (!preg_match($pattern, $car)) {
                $continue = false;
            }
        } while ($continue);
        $string = $car . $string;
    }

    static private function readKey(&$string) {
        $tmp = '';
        $key = '';

        $continue = true;
        do {
            $car = self::readCar($string);
            $tmp .= $car;
            if (preg_match("/[a-zA-Z0-9_\-.]/", $car)) {
                $key .= $car;
            } else {
                $continue = false;
            }
        } while ($continue);


        if (empty($key)) {
            $string = $tmp . $string;
            throw new Exception\ParseException('Erreur de lecture, une valeur était attendu dans :' . PHP_EOL . $string);
        } else {
            $string = $car . $string;
            self::skipCar($string, " \r\n\t");
        }

        return $key;
    }

    static private function asValue(&$string) {
        $asValue = false;
        self::skipCar($string, " \r\n\t");
        $car = self::readCar($string, false);
        if ($car == '=') {
            $asValue = true;
        }
        self::skipCar($string, " =\r\n\t");
        return $asValue;
    }

    static private function readValue(&$string) {
        $value = [];

        self::skipCar($string, " \r\n\t");
        $car = self::readCar($string);
        if ($car == '(') {
            do {
                array_push($value, self::readValue($string));
                if (self::readCar($string) != ')') {
                    throw new Exception\ParseException('Erreur de lecture, une \')\' était attendue.');
                }
                $car = self::readCar($string);
            } while ('(' == $car);
            $string = $car . $string;
        } else {
            $string = $car . $string;
            $key = self::readKey($string);
            if (self::asValue($string)) {
                $val = self::readValue($string);
                $value[$key] = $val;
            } else {
                $value = $key;
            }
        }

        self::skipCar($string, " \r\n\t");


        return $value;
    }

}
