@vite(['resources/sass/app.scss', 'resources/js/app.js'])



<div class="container">
    <br>
    <h3 class="text-center">Registro De Ponto</h3>
    <div class="d-flex justify-content-between align-content-center">

        <div class="col-1 my-3">

            <form action="" method="GET">
            @csrf

                <select class="form-select" name="filtro" aria-label="Default select example" onchange="this.form.submit()">
                    <option value="1" {{ (session('filtro') == '1') ? 'selected' : '' }}>Semana</option>
                    <option value="2" {{ (session('filtro') == '2') ? 'selected' : '' }}>Mês</option>
                    <option value="3" {{ (session('filtro') == '3') ? 'selected' : '' }}>Ano</option>
                </select>

            </form>

        </div>
        <div class="col-4 d-flex my-3">
            <input type="date" class="form-control" >

            <input type="date" class="form-control" >
        </div>

    </div>


    <table class="table  text-center table-secondary ">
        <thead class="table-dark  ">

        <tr class="rounded-pill" >
            <th scope="col">Nome</th>
            <th scope="col">Dia de Entrada</th>
            <th scope="col">Hora de Entrada</th>
            <th scope="col">Pausa</th>
            <th scope="col">Hora de Saida</th>
            <th scope="col">Dia da Saida</th>
            <th scope="col">Tempo</th>
            <th scope="col">Horas Extras</th>
        </tr>
        </thead>
        <tbody>
        @foreach($itens as $item)

            @if(\Carbon\Carbon::parse($item->entry_date)->isWeekend())
                <tr class="table-danger" >
                    <td > {{$item->user->name}} </td>
                    <td>{{ date('d-m-Y', strtotime( $item->entry_date))}}</td>

                    <td>Fim de semana</td>
                    <td>Fim de semana</td>
                    <td>Fim de semana</td>
                    <td>Fim de semana</td>
                    <td>Fim de semana</td>

                    <td>{{$item->getOverTime()}} h</td>
                </tr>
            @else
                <tr>
                    <td>{{$item->user->name}} </td>
                    <td>{{ date('d-m-Y', strtotime( $item->entry_date))}}</td>
                    <td>{{$item->entry_hour}}</td>
                    <td>{{$item->break_entry ." - ". $item->break_exit}}</td>
                    <td>{{$item->exit_hour}}</td>
                    <td>{{ date('d-m-Y', strtotime($item->exit_date)) }}</td>
                    <td class="{{$item->getHours() < "08:00" ? "text-danger" : "text-success"}}">{{$item->getHours()}} h</td>
                    <td>{{$item->getOverTime()}} h</td>
                </tr>
            @endif

        @endforeach
        </tbody>
    </table>
    <div class="row ms-2 gap-5">
        @foreach($hoursinfo as $key => $info)
            <div class="col-sm-2  text-center rounded  pt-4 h-75" style="background-color: #dedede; ">

                <p >{{$info[0]}}</p>
                <div class="d-flex justify-content-center">
                    <p class="{{$info[1][0] >= $info[1][1] ? 'text-success' : 'text-danger' }}">{{$info[1][0]}} </p> <pre> / </pre> <p> {{ $info[1][1]}}</p>
                </div>


            </div>
        @endforeach


    </div>

    <br>
{{--    {{$itens->links()}}--}}
</div>

