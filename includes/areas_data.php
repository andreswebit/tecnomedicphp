<?php
/**
 * Contenido de cada Área de negocio — extraído del documento de arquitectura.
 * Un solo lugar para editar textos; el template en areas/_template.php arma la página.
 */

function get_area(string $slug): ?array {
    $areas = [

        'audiologia' => [
            'nombre'      => 'Audiología',
            'icono'       => 'fa-headphones',
            'descripcion' => 'En TecnoMedic brindamos soluciones orientadas al diagnóstico, tratamiento y rehabilitación de la salud auditiva. Integramos atención profesional, tecnología especializada y una amplia línea de productos para acompañar las necesidades de pacientes, profesionales e instituciones.',
            'hacemos'     => [
                'Evaluación audiológica.',
                'Adaptación y programación de audífonos.',
                'Rehabilitación auditiva.',
                'Servicio técnico y mantenimiento.',
            ],
            'ofrecemos'   => [
                'Audífonos.',
                'Accesorios y conectividad.',
                'Moldes personalizados.',
                'Pilas y cargadores.',
                'Protectores auditivos.',
            ],
            'acciones'    => ['turno', 'productos', 'contacto'],
        ],

        'medicina-hiperbarica' => [
            'nombre'      => 'Medicina Hiperbárica',
            'icono'       => 'fa-lungs',
            'descripcion' => 'TecnoMedic ofrece tratamientos de oxigenoterapia hiperbárica mediante protocolos supervisados por profesionales, integrando tecnología especializada y seguimiento clínico para acompañar diferentes procesos terapéuticos y de recuperación.',
            'hacemos'     => [
                'Evaluación médica.',
                'Oxigenoterapia hiperbárica.',
                'Protocolos terapéuticos personalizados.',
            ],
            'ofrecemos'   => [
                'Programas terapéuticos.',
                'Recursos educativos.',
                'Información para pacientes y profesionales.',
            ],
            'nota'        => 'No se presenta como una tienda de productos, porque el valor principal es la prestación.',
            'acciones'    => ['turno', 'consultar', 'indicaciones'],
        ],

        'nutricion' => [
            'nombre'      => 'Nutrición',
            'icono'       => 'fa-apple-whole',
            'descripcion' => 'Abordamos la nutrición desde una perspectiva clínica, funcional y deportiva, integrando evaluación profesional, análisis de composición corporal y estrategias nutricionales personalizadas para acompañar objetivos de salud, prevención y rendimiento.',
            'hacemos'     => [
                'Consulta nutricional.',
                'Análisis de composición corporal.',
                'Planes nutricionales personalizados.',
            ],
            'ofrecemos'   => [
                'Suplementación funcional y deportiva.',
                'Recursos educativos.',
                'Programas de seguimiento.',
            ],
            'acciones'    => ['turno', 'miportal', 'suplementacion'],
        ],

        'ortopedia-rehabilitacion' => [
            'nombre'      => 'Ortopedia y Rehabilitación',
            'icono'       => 'fa-wheelchair',
            'descripcion' => 'Brindamos productos y servicios destinados a mejorar la movilidad, favorecer la recuperación funcional y acompañar los procesos de rehabilitación mediante soluciones adaptadas a cada necesidad.',
            'hacemos'     => [
                'Asesoramiento profesional.',
                'Adaptación de productos.',
                'Venta.',
                'Alquiler de equipamiento para rehabilitación.',
            ],
            'ofrecemos'   => [
                'Soportes articulares.',
                'Ayudas para la movilidad.',
                'Productos para rehabilitación.',
                'Descanso terapéutico.',
                'Compresión terapéutica.',
            ],
            'acciones'    => ['productos', 'presupuesto', 'whatsapp'],
        ],

        'equipamiento-medico' => [
            'nombre'      => 'Equipamiento Médico y Quirúrgico',
            'icono'       => 'fa-kit-medical',
            'descripcion' => 'Ofrecemos equipamiento médico, quirúrgico y respiratorio destinado al ámbito profesional, institucional y domiciliario, complementado con asesoramiento técnico, modalidades de venta y alquiler y soluciones adaptadas a cada proyecto.',
            'hacemos'     => [
                'Asesoramiento técnico.',
                'Venta.',
                'Alquiler de equipamiento médico.',
                'Instalación y capacitación.',
                'Oxigenoterapia domiciliaria.',
            ],
            'ofrecemos'   => [
                'Equipamiento médico.',
                'Equipamiento respiratorio.',
                'Instrumental quirúrgico.',
                'Implantes e insumos quirúrgicos.',
                'Equipamiento hospitalario y de consultorio.',
            ],
            'acciones'    => ['productos', 'presupuesto', 'contacto'],
        ],

    ];

    return $areas[$slug] ?? null;
}
