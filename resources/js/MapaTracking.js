$(document).ready(function() {
    // Lista de países prohibidos
    const paisesProhibidos = [
        'united states', 'united states of america', 'estados unidos de américa',
        'united kingdom', 'jamaica', 'china', 'canada', 'mexico', 'brazil', 'india', 
        'russia', 'australia', 'germany', 'france', 'italy', 'spain', 'japan', 'argentina',
        'colombia', 'chile', 'venezuela', 'peru', 'ecuador', 'bolivia', 'panama', 
        'guatemala', 'el salvador', 'honduras', 'nicaragua', 'cuba', 'haiti', 
        'dominican republic', 'south africa', 'nigeria', 'egypt', 'kenya', 'morocco', 
        'algeria', 'ghana', 'ethiopia', 'uganda', 'tanzania', 'senegal', 'tunisia', 
        'sudan', 'angola', 'cameroon', 'zimbabwe', 'libya', 'new zealand', 'fiji', 
        'hong kong', 'taiwan', 'palestine', 'greenland', 'guam', 'bermuda', 
        'martinique', 'guadeloupe', 'reunion'
    ];

    // Variables globales
    var eventos = window.eventos || [];
    
    
    var map = new L.map('map', { zoomControl: false }).setView([20, 0], 3);
    var tileLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors'
    }).addTo(map);
    var zoomControl = L.control.zoom({ position: 'topright' }).addTo(map);

    CargarDatosMapa();

    $("#CostaRica").change(function() {
        if ($(this).is(':checked')) {
            eventos.unshift({
                "DESCRIPCION": "Entregado a la dirección de Costa Rica.",
                "CODIGOPOSTAL": 0,
                "PAISESTADO": "Costa Rica",
                "OCULTADO": 0
            });
        } else {
            eventos.splice(eventos.findIndex(e => e.PAISESTADO === "Costa Rica"), 1);
        }
        CargarDatosMapa();
    });
    function ValidaMostrarFactura(activo) {
        activo == true ? $("#factura").removeAttr("hidden") : $("#factura").attr("hidden", "hidden");
        console.log("Valor de entregado:", $("#entregado").val()); // LOG del valor de entregado
    }
    ValidaMostrarFactura($("#entregado").is(':checked'));
    $("#entregado").change(function() {
        if ($(this).is(':checked')) {
            const selectedOption = $("#idDireccion").find(":selected");
            const value = selectedOption.data("value");
    
            
            if (value && value.trim() !== "") {
                eventos.unshift({
                    "DESCRIPCION": "Entregado a la dirección del cliente.",
                    "CODIGOPOSTAL": 0,
                    "PAISESTADO": "Costa Rica," + value,
                    "OCULTADO": 0
                });
            } else {
                console.warn("⚠️ No se encontró provincia y distrito seleccionados. No se agregó el evento.");
                alert("Por favor seleccione una dirección válida antes de marcar Entregado.");
                $(this).prop('checked', false); 
            }
        } else {
            const index = eventos.findIndex(e => e.PAISESTADO && e.PAISESTADO.startsWith("Costa Rica"));
            if (index !== -1) {
                eventos.splice(index, 1);
            }
        }
        ValidaMostrarFactura($(this).is(':checked'));
        CargarDatosMapa();
    });
    
    function CargarDatosMapa() {
        $('#mapaSpinner').hide();

        // Limpiar marcadores y líneas anteriores
        if (window.eventMarkers) {
            window.eventMarkers.forEach(marker => map.removeLayer(marker));
        }
        if (window.polyline) {
            map.removeLayer(window.polyline);
        }

        window.eventMarkers = [];
        var markerCoords = [];

        const geoIcon = L.divIcon({
            className: 'bootstrap-icon',
            html: '<i class="bi bi-geo-alt-fill" style="font-size: 24px; color: red;"></i>',
            iconSize: [30, 30],
            iconAnchor: [15, 30],
            popupAnchor: [0, -30]
        });

        async function addEventMarkers() {
            $('#mapaSpinner').show();

            for (const evento of eventos) {
                const { DESCRIPCION,DIRECCION, PAISESTADO, CODIGOPOSTAL } = evento;
                if (PAISESTADO) {
                    const coordinates = await getCoordinates(PAISESTADO, CODIGOPOSTAL);
                    if (coordinates) {
                        const { lat, lon } = coordinates;
                        markerCoords.push([lat, lon]);
                       
                            const marker = L.marker([lat, lon], { icon: geoIcon }).addTo(map)
                            .bindPopup(`<strong>${DESCRIPCION ?? DIRECCION}</strong><br>${PAISESTADO}<br>Código Postal: ${CODIGOPOSTAL}`);
                            window.eventMarkers.push(marker);
                        
                    }
                }
            }

            if (markerCoords.length > 0  ){
            
                    window.polyline = L.polyline(markerCoords, { color: 'red', weight: 4, opacity: 0.7 }).addTo(map);
                
                const bounds = L.latLngBounds(markerCoords);
                map.fitBounds(bounds, { padding: [50, 50] });
                const currentZoom = map.getZoom();
                if (currentZoom > 1) {
                    map.setZoom(6);
                }
               
            }else{
                map.setView([9.7489, -83.7534], 6);  // Coordenadas de Costa Rica + zoom 3.5

            }
            
            $('#mapaSpinner').hide();
        }

        addEventMarkers();
    }

    async function getCoordinates(city, postalCode) {
        try {
            if (postalCode == 0 || postalCode == null) postalCode = '';
            postalCode = String(postalCode);
            if (!isNaN(city)) city = '';

            if (city) {
                city = city.replace(/(Person,|International Hub|Distribution Center|Apt|ISC|Airport|-PTT|\(USPS\))/gi, '').trim();
            }

            let queries = [];
            if (city && postalCode) {
                if (city.includes(',')) {
                    queries.push(city);
                } else {
                    queries.push(`${city} ${postalCode}`);
                    queries.push(city);
                }
            } else if (city) {
                queries.push(city);
            } else if (postalCode) {
                queries.push(postalCode);
            } else {
                console.warn('City y postalCode vacíos, no se buscará.');
                return null;
            }

            for (const query of queries) {
                const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=2`;
                const response = await fetch(url);
                const data = await response.json();

                if (data && data.length > 0) {
                    const result = data[0];
                    const nombreLugar = result.display_name ? result.display_name.toLowerCase() : '';

                    if (!nombreLugar.includes('costa rica')) {
                        if (result.class === "boundary" && (result.type === "administrative" || result.type === "country")) {
                            const nombreSinComa = nombreLugar.split(',').length <= 2;
                            if (nombreSinComa && paisesProhibidos.some(pais => nombreLugar.includes(pais))) {
                                console.warn(`Se detectó que ${query} es un país prohibido puro (${nombreLugar}), no se mostrará.`);
                                return null;
                            }
                        }
                    }

                    const lat = parseFloat(result.lat);
                    const lon = parseFloat(result.lon);
                    return { lat, lon };
                }
            }

            console.error(`No se pudo obtener coordenadas para: ${city}, ${postalCode}`);
        } catch (error) {
            console.error(`Error al obtener coordenadas para ${city}, ${postalCode}:`, error);
        }
        return null;
    }
});
