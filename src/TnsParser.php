<?php

/*
 * The MIT License
 *
 * Copyright 2015 Fabien Sanchez fzed51@gmail.com.
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
    private $pointeur;
    private $oldPointeur;

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

    static function parseString($strTnsName) {
        $p = new self();
        $p->setTnsName($strTnsName);
        return $p->parse();
    }

    static function parseFile($fileName) {
        $p = new self();
        $p->setTnsNameFromFile($fileName);
        return $p->parse();
    }

    static private function cleanTnsName($strTnsName) {
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
        $tns = [];
        $this->initPointeur();

        /*
          $lignes = explode("\n", $this->strTnsName);
          foreach ($lignes as $ligne) {
          $netServiceName = self::readKey($ligne);
          self::asValue($ligne);
          $description = self::readValue($ligne);
          $netServiceNames = explode(',', $netServiceName);
          foreach ($netServiceNames as $serviceName) {
          $tns[$serviceName] = $description;
          }
          }
         */

        while (!self::isTheEnd()) {
            $netServiceName = $this->readKey();
            $this->asValue();
            $description = $this->readValue();
            $this->skipToEndLine();
            $netServiceNames = explode(',', $netServiceName);
            foreach ($netServiceNames as $serviceName) {
                $tns[$serviceName] = $description;
            }
        }

        return $tns;
    }

    private function initPointeur($pointeur = null) {
        $this->pointeur = $pointeur;
    }

    private function next() {
        if ($this->pointeur === null) {
            $this->pointeur = 0;
        } else {
            $this->pointeur++;
        }
        return (bool) !$this->isTheEnd();
    }

    private function back() {
        $this->pointeur--;
        return (bool) ($this->pointeur >= 0);
    }

    private function isTheEnd() {
        if (!is_null($this->pointeur)) {
            if ($this->pointeur >= strlen($this->strTnsName)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function readCar() {
        if ($this->next()) {
            return $this->strTnsName[$this->pointeur];
        }
        return null;
    }

    private function skipCar($cars = " \t") {
        $pattern = "/[$cars]/";
        while (!$this->isTheEnd() && preg_match($pattern, $this->readCar())) {
            // pass
        }
        $this->back();
        return (bool) !$this->isTheEnd();
    }

    private function skipToEndLine() {
        $start = $this->pointeur;
        $this->skipCar();
        if (($this->readCar() === "\n") || $this->isTheEnd()) {
            return true;
        } else {
            $this->initPointeur($start);
            return false;
        }
    }

    private function viewScop($long = 20) {
        $start = max([0, ($this->pointeur - $long)]);
        $end = min([($this->pointeur + $long), (strlen($this->strTnsName) - 1)]);
        return substr($this->strTnsName, $start, $this->pointeur - $start) . '!>' . substr($this->strTnsName, $this->pointeur, $end - $this->pointeur);
    }

    private function readKey() {
        $key = '';
        $start = $this->pointeur;

        $continue = true;
        do {
            $car = $this->readCar();
            if (!is_null($car) && preg_match("/[a-zA-Z0-9_\-.]/", $car) > 0) {
                $key .= $car;
            } else {
                $this->back();
                $continue = false;
            }
        } while ($continue);

        if (empty($key)) {
            $this->initPointeur($start);
            throw new Exception\ParseException('Erreur de lecture, une valeur était attendu dans :' . PHP_EOL . $this->viewScop() . PHP_EOL);
        }

        return $key;
    }

    private function asValue() {
        $asValue = false;
        $this->skipCar(" \r\n\t");
        if ($this->readCar() == '=') {
            $asValue = true;
            $this->skipCar(" =\r\n\t");
        } else {
            $this->back();
        }

        return $asValue;
    }

    private function readValue() {
        $value = [];

        $this->skipCar(" \r\n\t");
        $car = $this->readCar();
        if ($car == '(') {
            do {
                array_push($value, $this->readValue());
                $this->skipCar(" \r\n\t");
                if ($this->readCar() != ')') {
                    throw new Exception\ParseException('Erreur de lecture, une \')\' était attendue dans :' . PHP_EOL . $this->viewScop(40) . PHP_EOL);
                }
                $this->skipCar(" \r\n\t");
                $car = $this->readCar();
            } while ('(' == $car);
            $this->back();
            if (count($value) == 1) {
                $value = $value[0];
            }
        } else {
            $this->back();
            $key = $this->readKey();
            if ($this->asValue()) {
                $val = $this->readValue();
                $value[$key] = $val;
            } else {
                $value = $key;
            }
        }

        $this->skipCar(" \r\n\t");

        return $value;
    }

}
