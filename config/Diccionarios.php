<?php

class defaultObject
{
    public $NOMBRE;

    public $id;

    public function __construct($nombre, $id)
    {
        $this->NOMBRE = $nombre;
        $this->id = $id;
    }
}
class Diccionarios
{
    public static $courrier = [];

    public static $provincias = [];

    public static $tiposDirecciones = [];

    public static function initialize()
    {

        self::$courrier = [
            new defaultObject('Yun Express', 1),
            new defaultObject('Yanwen Logistics', 2),
            new defaultObject('XPO', 3),
            new defaultObject('USPS, GLS, PARCELFORCE, EMS', 4),
            new defaultObject('USPS', 5),
            new defaultObject('UPS', 6),
            new defaultObject('UniExpress', 7),
            new defaultObject('SpeedX', 8),
            new defaultObject('Panther Premium Logistics', 9),
            new defaultObject('OnTrac', 10),
            new defaultObject('DHL Express', 11),
            new defaultObject('DHL', 12),
            new defaultObject('Cainiao', 13),
            new defaultObject('Amazon Logistics', 14),
            new defaultObject('Malaysia Post', 15),
            new defaultObject('FedEx', 16),
            new defaultObject('GLS', 17),
            new defaultObject('Parcelforce', 18),
            new defaultObject('EMS', 19),
            new defaultObject('GoFo Express', 20),
            new defaultObject('', 21), // Definir con luis
            new defaultObject('Aeropost', 22),

        ];
        self::$provincias = [
            new defaultObject('Cartago', 1),
            new defaultObject('Guanacaste', 2),
            new defaultObject('Heredia', 3),
            new defaultObject('Limón', 4),
            new defaultObject('Puntarenas', 5),
            new defaultObject('San José', 6),
            new defaultObject('Alajuela', 7),
        ];

        self::$tiposDirecciones = [
            new defaultObject('Principal', 1),
            new defaultObject('Secundaria', 2),
        ];

    }

    public static function getDiccionario($grado)
    {
        self::initialize();
        switch ($grado) {
            case 'courrier':
                return self::$courrier;
                break;
            case 'provincias':
                return self::$provincias;
                break;
            case 'tiposDirecciones':
                return self::$tiposDirecciones;
                break;
            default:
                break;
        }
    }
}
