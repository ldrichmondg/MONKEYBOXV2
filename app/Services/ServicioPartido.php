<?php 

namespace App\Services;

use App\Events\EventoPartidoEliminado;
use App\Events\EventoProcesarCrearPartido;
use App\Events\EventoProcesarModificarPartido;
use App\Mail\EmailNuevoPartido;
use App\Models\Partidos;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Mail;

class ServicioPartido{

    protected $servicioEncargado;

    public function __construct(ServicioEncargado $servicioEncargado)
    {
        $this->servicioEncargado = $servicioEncargado;
    }

    public function Eliminar($request){

        try{
            
            $partido = Partidos::find($request->idPartido);
            //obtener todos los correos de los responsables que sus futbolistas pertenezcan a esa subcategoria
            $todosResponsables = $this->servicioEncargado->ObtenerResponsablesPrincipalesSubcategoria($partido->SUBCATEGORIA_ID);

            $evento = EventoPartidoEliminado::dispatch($partido, $todosResponsables);
            Partidos::destroy($request->idPartido);

            return true;
        } catch(Exception $e){
            return $e->getMessage();
        }
    }

    public function filter($request)
    {
        $query = $request->input('query');

        $partidos = Partidos::select('id', 'SUBCATEGORIA_ID', 'FECHA', 'LUGAR', 'EQUIPOCONTRINCANTE');

        if (! empty($query)) {
            $partidos->where(function ($q) use ($query) {
                $q->where('FECHA', 'like', "%{$query}%")
                    ->orWhere('LUGAR', 'like', "%{$query}%")
                    ->orWhere('EQUIPOCONTRINCANTE', 'like', "%{$query}%")
                        ->orWhereHas('subcategoria.categoria.sede', function ($subq) use ($query) {
                            
                            $subq->where('NOMBRE', 'like', "%{$query}%");
                        });
            });
        }
        //filtro sede
        $sede = $request->sede;

        if (isset($sede) && !empty($sede)){
            $partidos->where(function ($q) use ($sede) {
                $q->whereHas('subcategoria.categoria.sede', function ($subq) use ($sede) {
                    
                    $subq->where('id', 'like', "%{$sede}%");
                });
            });
        }

        //filtro subcategoria
        $categoria = $request->categoria;

        if (isset($categoria) && !empty($categoria)){
            $partidos->where(function ($q) use ($categoria) {
                $q->whereHas('subcategoria', function ($subq) use ($categoria) {
                    
                    $subq->where('id', 'like', "%{$categoria}%");
                });
            });
        }

        //filtro fecha
        $fechaAntes = $request->fechaAntes;
        $fechaDespues = $request->fechaDespues;

        if (isset($fechaAntes) && !empty($fechaAntes) && isset($fechaDespues) && !empty($fechaDespues)){
            $partidos->where(function ($q) use ($fechaAntes, $fechaDespues) {
                $q->whereDate('FECHA', '>=', $fechaAntes)
                    ->whereDate('FECHA', '<=', $fechaDespues);
            });
        }

        $resultado = $partidos->get();

        foreach($resultado as $partido){
            $partido->SEDE = $partido->subcategoria->categoria->sede->NOMBRE;
            $partido->SUBCATEGORIA_ID = $partido->subcategoria->categoriaSubcategoriaParentesis()->NOMBRE;
        }

        return response()->json($resultado);
    }

    public function Crear($request){
        try{
            $partido = new Partidos();

            $partido->SUBCATEGORIA_ID = $request->categorias;
            $partido->FECHA = $request->fecha;
            $partido->HORA = $request->hora;
            $partido->LUGAR = $request->lugar;
            $partido->LUGARLINK = $request->lugarLink;
            $partido->DETALLE = $request->detalle;
            $partido->EQUIPOCONTRINCANTE = $request->equipoContrincante;

            $partido->save();

            //obtener todos los correos de los responsables que sus futbolistas pertenezcan a esa subcategoria
            $todosResponsables = $this->servicioEncargado->ObtenerResponsablesPrincipalesSubcategoria($partido->SUBCATEGORIA_ID);
            EventoProcesarCrearPartido::dispatch($partido,$todosResponsables); //DISPATCH no me retorna el evento en sí xq es asincronico, pero dispatchNow si. eso usarlo al debuguear

            return true;

        } catch(Exception $e){
            return $e->getMessage();
        }
    }

    public function Actualizar($request): bool{
        try{
            $partido = Partidos::find($request->idPartido);           
            $partidoViejo = clone $partido;

            $partido->SUBCATEGORIA_ID = $request->categorias;
            $partido->FECHA = $request->fecha;
            $partido->HORA = $request->hora;
            $partido->LUGAR = $request->lugar;
            $partido->LUGARLINK = $request->lugarLink;
            $partido->DETALLE = $request->detalle;
            $partido->EQUIPOCONTRINCANTE = $request->equipoContrincante;

            $partido->save();

            //obtener todos los responsables tanto viejos (pertenecen a la subcategoria vieja) y los nuevos
            $todosResponsables = $this->servicioEncargado->ObtenerResponsablesPrincipalesSubcategoria($partido->SUBCATEGORIA_ID);
            $todosResponsablesViejo = $this->servicioEncargado->ObtenerResponsablesPrincipalesSubcategoria($partidoViejo->SUBCATEGORIA_ID);
            EventoProcesarModificarPartido::dispatch($partido, $partidoViejo, $todosResponsables, $todosResponsablesViejo);

            return true;
        } catch(Exception $e){
            return false;
        }
    }
}
