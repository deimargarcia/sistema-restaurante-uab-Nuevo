(() => {
    const boton = document.getElementById('dashboardMenuBoton');
    const barra = document.getElementById('dashboardSidebar');
    const fondo = document.getElementById('dashboardOverlay');

    if (!boton || !barra || !fondo) return;

    const esMovil = window.matchMedia('(max-width: 900px)').matches;
    boton.setAttribute('aria-expanded', String(!esMovil && !document.body.classList.contains('sidebar-contraido')));
    boton.setAttribute('aria-label', esMovil ? 'Abrir módulos' : 'Contraer módulos');
    boton.setAttribute('title', esMovil ? 'Abrir módulos' : 'Contraer módulos');

    const cerrar = () => {
        barra.classList.remove('abierto');
        fondo.classList.remove('visible');
        boton.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('menu-dashboard-abierto');
    };

    boton.addEventListener('click', () => {
        if (window.matchMedia('(max-width: 900px)').matches) {
            const abierto = barra.classList.toggle('abierto');
            fondo.classList.toggle('visible', abierto);
            boton.setAttribute('aria-expanded', String(abierto));
            boton.setAttribute('aria-label', abierto ? 'Cerrar módulos' : 'Abrir módulos');
            boton.setAttribute('title', abierto ? 'Cerrar módulos' : 'Abrir módulos');
            document.body.classList.toggle('menu-dashboard-abierto', abierto);
            return;
        }

        const contraida = document.body.classList.toggle('sidebar-contraido');
        boton.setAttribute('aria-expanded', String(!contraida));
        boton.setAttribute('aria-label', contraida ? 'Expandir módulos' : 'Contraer módulos');
        boton.setAttribute('title', contraida ? 'Expandir módulos' : 'Contraer módulos');
    });
    fondo.addEventListener('click', cerrar);
    document.addEventListener('keydown', (evento) => {
        if (evento.key === 'Escape') cerrar();
    });
})();
