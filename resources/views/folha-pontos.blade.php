@vite(['resources/sass/app.scss', 'resources/js/app.js'])


<div class="container">
    <br>
    <h3 class="text-center">Registro De Ponto</h3>
    <div class="d-flex justify-content-between align-content-center">

        <div class="col-2 mt-3">
            <p>Filtro</p>
            <form action="" method="GET">
                @csrf

                <select class="form-select" name="filtro" aria-label="Default select example"
                        onchange="this.form.submit()">
                    <option value="1" {{ (session('filtro') == '1') ? 'selected' : '' }}>Esta Semana</option>
                    <option value="2" {{ (session('filtro') == '2') ? 'selected' : '' }}>Última Semana</option>
                    <option value="3" {{ (session('filtro') == '3') ? 'selected' : '' }}>Este Mês</option>
                    <option value="4" {{ (session('filtro') == '4') ? 'selected' : '' }}>Último Mês</option>
                    <option value="5" {{ (session('filtro') == '5') ? 'selected' : '' }}>Ano</option>

                </select>

            </form>

        </div>
        <div class="col-4 d-flex align-content-center mt-3">
            <form action="" method="GET" class="d-flex input-group input-group-sm mb-3 h-50">
                <div class="col">
                    <p>Inicio</p>
                    <input type="date" class="form-control" name="entry_date" value="{{ (session('filtroDate')['entry_date'] ?? '') }}" required>
                </div>

                <div class="col">
                    <p>Fim</p>
                    <input type="date" class="form-control" name="exit_date"   value="{{ (session('filtroDate')['exit_date'] ?? '') }}" required>
                </div>
                <div class="col">
                    <p>Pesquisar</p>
                    <button class="btn bg-success  ms-2">GO</button>
                </div>

            </form>
        </div>


    </div>

    <div>

        <table class="table  text-center table-secondary ">
            <thead class="table-dark  ">

            <tr class="rounded-pill">
                <th scope="col">Nome</th>
                <th scope="col">Dia de Entrada</th>
                <th scope="col">Hora de Entrada</th>
                <th scope="col">Pausa</th>
                <th scope="col">Hora de Saida</th>
                <th scope="col">Dia da Saida</th>
                <th scope="col">Horas Extras</th>
                <th scope="col">Tempo</th>

            </tr>
            </thead>
            <tbody>
            @foreach($itens as $item)

                @if(\Carbon\Carbon::parse($item->entry_date)->isWeekend())
                    <tr class="table-danger">
                        <td> {{$item->user->name}} </td>
                        <td>{{ date('d-m-Y', strtotime( $item->entry_date))}}</td>

                        <td>Fim de semana</td>
                        <td>Fim de semana</td>
                        <td>Fim de semana</td>
                        <td>Fim de semana</td>
                        <td>{{$item->getOverTime()}} h</td>
                        <td>Fim de semana</td>


                    </tr>
                @else
                    <tr>
                        <td>{{$item->user->name}} </td>
                        <td>{{ date('d-m-Y', strtotime( $item->entry_date))}}</td>
                        <td>{{$item->entry_hour}}</td>
                        <td>{{$item->break_entry ." - ". $item->break_exit}}</td>
                        <td>{{$item->exit_hour}}</td>
                        <td>{{ date('d-m-Y', strtotime($item->exit_date)) }}</td>
                        <td>{{$item->getOverTime()}} h</td>
                        <td class="{{$item->getHours() < "08:00" ? "text-danger" : "text-success"}}">{{$item->getHours()}}
                            h
                        </td>

                    </tr>
                @endif

            @endforeach


            </tbody>
            <thead >
                <tr>
                    <th colspan="7" class="table-light " style="border: none !important;"></th>
                    <th >Total de Horas</th>
                </tr>
            </thead>
            <tbody>
            <tr>
                <td colspan="7" class="table-light" style="border: none !important;"></td>

                <td>{{$totalHoursOfList}}</td>
            </tr>
            <tbody>


            </tbody>

        </table>


    </div>


    <div class="row ms-2 gap-5">
        @if($hoursinfo)
            @foreach($hoursinfo as $info)
                <div class="col-sm-2  text-center rounded  pt-4 h-75" style="background-color: #dedede; ">

                    <p>{{$info['title']}}</p>
                    <div class="d-flex justify-content-center">
                        <p class="{{ intval($info['hours'] ) >= intval($info['hoursrequired'])  ? 'text-success' : 'text-danger' }}">{{$info['hours']}} </p>
                        <pre> / </pre>
                        <p> {{ $info['hoursrequired']}}</p>
                    </div>


                </div>
            @endforeach
        @else
            <p>Sem dados</p>
        @endif



    </div>

    <br>
    {{--    {{$itens->links()}}--}}
</div>

