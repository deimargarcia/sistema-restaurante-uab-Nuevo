(() => {
    const boton = document.getElementById('dashboardMenuBoton');
    const barra = document.getElementById('dashboardSidebar');
    const fondo = document.getElementById('dashboardOverlay');

    if (!boton || !barra || !fondo) return;

    const cerrar = () => {
        barra.classList.remove('abierto');
        fondo.classList.remove('visible');
        boton.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('menu-dashboard-abierto');
    };

    boton.addEventListener('click', () => {
        const abierto = barra.classList.toggle('abierto');
        fondo.classList.toggle('visible', abierto);
        boton.setAttribute('aria-expanded', String(abierto));
        document.body.classList.toggle('menu-dashboard-abierto', abierto);
    });
    fondo.addEventListener('click', cerrar);
    document.addEventListener('keydown', (evento) => {
        if (evento.key === 'Escape') cerrar();
    });
})();
