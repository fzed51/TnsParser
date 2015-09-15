TnsParser
=========

TnsParser est une classe qui permet de lire un fichier TNSNAME.ora d'Oracle.

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
