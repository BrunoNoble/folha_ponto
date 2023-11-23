@vite(['resources/sass/app.scss', 'resources/js/app.js'])

<div class="container">
    <div class="d-flex flex-column justify-content-center align-content-center text-center mt-3">
        <h1>Registro Dia</h1>
    @if(session('register') === "break_entry")
        <form action="" method="get">

            <input value="breakEntry" name="conditions" type="hidden">
            <input value="{{\Carbon\Carbon::now()}}" name="time" type="hidden">
            <button type="submit" class="btn btn-success mt-3">Break Time</button>

        </form>
    @elseif(session('register') === "break_exit")
        <form action="" method="get">

            <input value="breakExit" name="conditions" type="hidden">
            <input value="{{\Carbon\Carbon::now()}}" name="time" type="hidden">
            <button type="submit" class="btn btn-danger mt-3">Break Exit</button>

        </form>
    @elseif(session('register') === "exit")
        <form action="" method="get">

            <input value="exit" name="conditions" type="hidden">
            <input value="{{\Carbon\Carbon::now()}}" name="time" type="hidden">
            <button type="submit" class="btn btn-danger mt-3">Encerrar</button>

        </form>
    @else
        <form action="" method="get">

            <input value="startDay" name="conditions" type="hidden">
            <input value="{{\Carbon\Carbon::now()}}" name="time" type="hidden">
            <button type="submit" class="btn btn-success mt-3">Iniciar</button>

        </form>
    @endif
    </div>
</div>
