TnsParser
=========

TnsParser est une classe PHP qui permet de lire un fichier TNSNAME.ora d'Oracle.

## Exemple :

```php
<?php
$tnsFile = TnsParser:parseFile('C:\Oracle\Tns_Admin\TNSNAME.ora');
$tnsString = TnsParser:parseString(
"MYSERVICE=(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=localhost)(PORT=1521))(CONNECT_DATA=(SERVICE_NAME=ORCL)))"
);
```

$tnsString pourait retourner :

```
Array
(
    [MYSERVICE] => Array
        (
            [0] => Array
                (
                    [DESCRIPTION] => Array
                        (
                            [0] => Array
                                (
                                    [ADDRESS] => Array
                                        (
                                            [0] => Array
                                                (
                                                    [PROTOCOL] => TCP
                                                )
                                            [1] => Array
                                                (
                                                    [HOST] => localhost
                                                )
                                            [2] => Array
                                                (
                                                    [PORT] => 1521
                                                )
                                        )
                                )
                            [1] => Array
                                (
                                    [CONNECT_DATA] => Array
                                        (
                                            [0] => Array
                                                (
                                                    [SERVICE_NAME] => ORCL
                                                )
                                        )
                                )
                        )
                )
        )
)
```

## Licence

The MIT License (MIT)

Copyright (c) 2015 Fabien Sanchez fzed51@gmail.com

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.