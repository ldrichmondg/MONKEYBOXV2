<?php

namespace App\Actions;

class ActionDepurarRegex
{
    public static function execute(string $regex): string
    {
        // 1. Verificar si el primer y ultimo char tienen / o /i
        // 2. Si tienen, se retorna sin cambios
        // 3. Si no tienen, se agregan

        // 1. Verificar si el primer y ultimo char tienen / o /i
        $regexDepurado = $regex;

        $ultimoChar = substr($regex, -1);
        $primerChar = $regex[0];

        // - Validaciones del primero
        if ($primerChar != '/') {
            $regexDepurado = '/'.$regexDepurado;
        }

        // - Validaciones del ultimo
        if ($ultimoChar == '/') {
            $regexDepurado = $regexDepurado.'i';
        } elseif ($ultimoChar != 'i') {
            $regexDepurado = $regexDepurado.'/i';
        }

        return $regexDepurado;
    }
}
