<?php

namespace App\Http\Controllers;

use App\Models\FolhaPontos;
use App\Http\Requests\StoreFolhaPontosRequest;
use App\Http\Requests\UpdateFolhaPontosRequest;
use Carbon\Carbon;
use DateTime;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Collection;

//senha 3\8pi#t^P5(57U&RYUY&
class FolhaPontosController extends Controller
{
    public function registerPonto(Request $request)
    {


        if ($request->all())
        {
            $date = Carbon::create($request->input('time')) ;

            switch ($request['conditions'])
            {
                case "startDay":

                    $register = new FolhaPontos();
                    $register->user_id = 1;
                    $register->entry_date = $date->format('Y-m-d');
                    $register->exit_date = null;
                    $register->entry_hour = $date->format('H:i:s');
                    $register->exit_hour = null;
                    $register->break_entry = null;
                    $register->break_exit = null;
                    $register->save();


                    Session::put('id_register', $register->id);

                    Session::put('register', 'break_entry');
                    break;
                case "breakEntry":
                    if (Session::get('id_register') !== null)
                    {
                        $registerCurrent = FolhaPontos::where('id',Session::get('id_register'))->first();
                        if ($registerCurrent !== null){
                            $registerCurrent->break_entry = $date->format('H:i:s');
                            $registerCurrent->save();
                        }
                    }


                    Session::put('register', 'break_exit');
                    break;
                case "breakExit":
                    if (Session::get('id_register') !== null)
                    {
                        $registerCurrent = FolhaPontos::where('id',Session::get('id_register'))->first();
                        if ($registerCurrent !== null) {

                            $registerCurrent->break_exit = $date->format('H:i:s');
                            $registerCurrent->save();
                        }

                    }

                    Session::put('register', 'exit');
                    break;
                case "exit":
                    if (Session::get('id_register') !== null)
                    {
                        $registerCurrent = FolhaPontos::where('id',Session::get('id_register'))->first();
                        if ($registerCurrent !== null) {
                            $registerCurrent->exit_hour = $date->format('H:i:s');
                            $registerCurrent->exit_date = $date->format('Y-m-d');
                            $registerCurrent->save();
                        }
                    }


                    Session::put('register', '');
                    break;
            }

        }
       ;
        return view('register-ponto',[

        ]);
    }


    /**
     * obtem as informacoes necessarias para a view folha de pontos.
     */
    public function index(Request $request)
    {

        //Primeiro filtro semana mes ano
        $firstFilter = $request->input('filtro');
        //Segundo filtro date x date
        $filterOfDate = $request->all();
        //adiciona variavel de sessao para manter filtro selecionado
        Session::put('filtro', $firstFilter);
        //adiciona variavel de sessao para manter data selecionada
        Session::put('filtroDate', $filterOfDate);

        //Verifica se foi feito algum filtro
        if ($firstFilter)
        {
            $itens = $this->getFilterData($firstFilter); //obtem dados conforme filtro

        }
        else if ($filterOfDate) //Verifica se foi feito  filtro por date
        {
            //Obtem dados conforme das datas passadas
           $itens = FolhaPontos::whereBetween('entry_date', [$filterOfDate['entry_date'],$filterOfDate['exit_date']])->get();

        }else
        {
            //filtra por padrao a semana
            $itens = $this->getFilterData($firstFilter ? : '' );
        }

        //Obtem o total de horas conforme os itens filtrados
        $totalHoursOfItens = $this->sumHours($itens);

        //Se existir dados na filtragem
        if ($itens->first())
        {

            $date = Carbon::create($itens->first()->entry_date);

        //Caso nao tenha dados
        }else
        {
            $date = null;
        }


        //retorna dados a view
        return view('folha-pontos',[
            'itens'=>$itens,
            'hoursinfo' => $this->getInfoForCards(dateRequired: $date ),
            'totalHoursOfList'=> $totalHoursOfItens


        ]);
    }
    /**
     * Filtra os dados do tipo Folha de Ponto com base na string fornecida.
     *
     * @param string $filtro Parâmetro para filtragem.
     *
     * @return \Illuminate\Support\Collection Coleção dos dados filtrados do tipo FolhaPonto.
     */

    private function getFilterData(string $filtro) : Collection
    {
        $dateCurrent = Carbon::now();

        switch ($filtro)
        {
            //dados desta semana
            case '1':
                $startDate = $dateCurrent->startOfWeek()->format('Y-m-d');
                $endDate = $dateCurrent->endOfWeek()->format('Y-m-d');
                break;
                //dados da semana anterior
            case '2':
                $dateCurrent->subWeek();
                $startDate = $dateCurrent->startOfWeek()->format('Y-m-d');
                $endDate = $dateCurrent->endOfWeek()->format('Y-m-d');
                break;
                //dados deste mes
            case '3':
                $startDate = $dateCurrent->firstOfMonth()->format('Y-m-d');
                $endDate = $dateCurrent->lastOfMonth()->format('Y-m-d');
                break;
                //dados mes anterior
            case '4':
                $dateCurrent = Carbon::now()->subMonth();

                $startDate = $dateCurrent->firstOfMonth()->format('Y-m-d');
                $endDate = $dateCurrent->lastOfMonth()->format('Y-m-d');
                break;
                //dados deste ano
            case '5':
                $startDate = $dateCurrent->firstOfYear()->format('Y-m-d');
                $endDate = $dateCurrent->lastOfYear()->format('Y-m-d');
                break;
                //dados por padrao desta semana
            default:
                $startDate = $dateCurrent->startOfWeek()->format('Y-m-d');
                $endDate = $dateCurrent->endOfWeek()->format('Y-m-d');

                break;
        }

        //obetem dados pela filtragem
        $points = FolhaPontos::whereBetween('entry_date', [$startDate, $endDate])->get();

        return $points; //retorna Collection de Folha de pontos
    }

    /**
     * A função preenche dados necessários para cards com base na condição fornecida.
     *
     * @param int $conditions Número da condição a ser processada (de 1 a 5).
     * @param array|null $data Dados adicionais que podem ser fornecidos (opcional).
     * @return array Retorna um array contendo os dados preenchidos para os cards.
     */


    private function getInfoForCards(int $conditions = 1 , array &$data = null, Carbon $dateRequired = null): array
    {

        if (!$dateRequired) {
            return [];
            //tetando
            // Switch para diferentes períodos do ano
//            switch ($conditions) {
//                case 1:
//                    // Pegando a data atual
//                    $currentDate = Carbon::now();
//
//                    // Obtendo o início e o fim da semana com base na data atual
//                    $startDate = $currentDate->startOfWeek()->format('Y-m-d');
//                    $endDate = $currentDate->endOfWeek()->format('Y-m-d');
//
//                    // Título para o card
//                    $title = "Esta Semana";
//                    break;
//                case 2:
//                    // Pegando a data da semana passada
//                    $currentDate = Carbon::now()->subWeek();
//
//                    // Obtendo o início e o fim da semana com base na data da semana passada
//                    $startDate = $currentDate->startOfWeek()->format('Y-m-d');
//                    $endDate = $currentDate->endOfWeek()->format('Y-m-d');
//
//                    // Título para o card
//                    $title = "Última Semana";
//                    break;
//                case 3:
//                    // Pegando a data atual
//                    $currentDate = Carbon::now();
//
//                    // Obtendo o primeiro e o último dia do mês com base na data atual
//                    $startDate = $currentDate->firstOfMonth()->format('Y-m-d');
//                    $endDate = $currentDate->lastOfMonth()->format('Y-m-d');
//
//                    // Título para o card
//                    $title = "Este Mês";
//                    break;
//                case 4:
//                    // Pegando a data do mês anterior
//                    $currentDate = Carbon::now()->subMonth();
//
//                    // Obtendo o primeiro e o último dia do mês com base na data do mês anterior
//                    $startDate = $currentDate->firstOfMonth()->format('Y-m-d');
//                    $endDate = $currentDate->lastOfMonth()->format('Y-m-d');
//
//                    // Título para o card
//                    $title = "Último Mês";
//                    break;
//                case 5:
//                    // Pegando a data atual
//                    $currentDate = Carbon::now();
//
//                    // Obtendo o primeiro e o último dia do ano com base na data atual
//                    $startDate = $currentDate->firstOfYear()->format('Y-m-d');
//                    $endDate = $currentDate->lastOfYear()->format('Y-m-d');
//
//                    // Título para o card
//                    $title = "Total";
//                    break;
//            }
        } else {
            // Switch para diferentes períodos do ano

            switch ($conditions) {
                case 1:
                    $datecurrent = clone $dateRequired;
                    // Obtendo o início e o fim da semana com base na data atual
                    $startDate = $datecurrent->startOfWeek()->format('Y-m-d');
                    $endDate = $datecurrent->endOfWeek()->format('Y-m-d');

                    // Título para o card
                    $title = "Esta Semana";
                    break;
                case 2:
                    // Pegando a data da semana passada

                    $datecurrent = clone $dateRequired;
                    $datecurrent->subWeek();
                    // Obtendo o início e o fim da semana com base na data da semana passada
                    $startDate = $datecurrent->startOfWeek()->format('Y-m-d');
                    $endDate = $datecurrent->endOfWeek()->format('Y-m-d');

                    // Título para o card
                    $title = "Última Semana";
                    break;
                case 3:

                    $datecurrent = clone $dateRequired;

                    // Obtendo o primeiro e o último dia do mês com base na data atual
                    $startDate = $datecurrent->firstOfMonth()->format('Y-m-d');
                    $endDate = $datecurrent->lastOfMonth()->format('Y-m-d');

                    // Título para o card
                    $title = "Este Mês";

                    break;
                case 4:
                    // Pegando a data do mês anterior
                    $datecurrent = clone $dateRequired;
                    $datecurrent->subMonth();

                    // Obtendo o primeiro e o último dia do mês com base na data do mês anterior
                    $startDate = $datecurrent->firstOfMonth()->format('Y-m-d');
                    $endDate = $datecurrent->lastOfMonth()->format('Y-m-d');

                    // Título para o card
                    $title = "Último Mês";


                    break;
                case 5:

                    $datecurrent = clone $dateRequired ;

                    // Obtendo o primeiro e o último dia do ano com base na data atual
                    $startDate = $datecurrent->firstOfYear()->format('Y-m-d');
                    $endDate = $datecurrent->lastOfYear()->format('Y-m-d');

                    // Título para o card
                    $title = "Total";


                    break;
            }

        }


        //Obtem as horas requeridas daquele periodo
        $hoursRequired = $this->getHoursRequired($startDate,$endDate);

        //filtro a db baseado na switch
        $days = FolhaPontos::whereBetween('entry_date', [$startDate, $endDate])->get();


        //obtem as horas contabilizadas durante aquele periodo e formatada em string
        $formatedHours =  $this->sumHours($days);

        //Array de dados a serem utilizados na view
        $data[] = ['title'=> $title, 'hours'=> $formatedHours,'hoursrequired'=> $hoursRequired ];

        // Se o número de condições ainda for menor que 5 (1 a 4), então há mais condições para processar
        if ($conditions < 5)
        {
            // Incrementa o valor de $conditions para avançar para a próxima condição
            $conditions ++;

            // Chama recursivamente a função atual, passando a próxima condição e os dados existentes
            $this->getInfoForCards($conditions, $data,$dateRequired);
        }


        return $data;
    }


    /**
     * Soma as horas trabalhadas de um período específico.
     *
     * @param \Illuminate\Support\Collection $days Coleção com os dias do período a serem somadas as horas.
     *
     * @return string Retorna o total de horas somadas e formatadas.
     */


    private function sumHours(Collection $days) :string
    {

        //declaracao variavel
        $totralMinutesOfDay = 0;

        foreach ($days as $day)
        {
            //obtem a horas feitas no dia
            $stringHoursOfDay = $day->getHours();

            //separa as horas e minutos
            list($hour, $minutes) = explode(':', $stringHoursOfDay);

            //transforma hora em minutos e soma aos minutos
            $totalMinutes = $hour * 60 + $minutes;

            //incrementa a variavel
            $totralMinutesOfDay += $totalMinutes;

        }
        //convertendo minutos em horas
        $hours = floor($totralMinutesOfDay / 60);
        //obetend apenas minutos
        $minutes = $totralMinutesOfDay % 60;

        //formatando a string
        $formatedHours = sprintf( "%02d:%02d",$hours, $minutes);

        //retorna o total
        return $formatedHours;
    }

    /**
     * Obtém, por meio de uma API, todos os feriados do ano com base em um código de identificação.
     *
     * @return array Retorna um array contendo todas as datas de feriados.
     */

    private function getHolidays(int $year)
    {




        try {
            // Definindo a chave de API (substitua 'YOUR_API_KEY' pela sua chave real)
            $apiKey = 'YOUR_API_KEY';

            // Definindo o código do país para Portugal
            $countryCode = 'PT';

            // Criando uma nova instância do cliente Guzzle HTTP
            $client = new Client();

            // Fazendo uma requisição GET para a API de feriados
            $response = $client->get("https://date.nager.at/Api/v2/PublicHolidays/".$year."/{$countryCode}?apikey={$apiKey}");
            // Decodificando a resposta JSON para um array associativo
            $holidays = json_decode($response->getBody(), true);

            $holidaysDates = [];

            foreach ($holidays as $key => $day)
            {

                $holidaysDates[] = $day['date'];


            }

            // Retornando a view 'holidays' com os feriados compactados para serem usados na view
            return $holidaysDates;
        }catch (\Exception $e)
        {
            return response()->json(['error' => 'Ocorreu um erro ao obter os feriados.'], 500);

        }




    }



    /**
     * Calcula as horas requeridas entre duas datas, excluindo finais de semana e feriados.
     *
     * @param string $dateStart Data de início no formato 'YYYY-MM-DD'.
     * @param string $dateEnd Data de término no formato 'YYYY-MM-DD'.
     *
     * @return string Retorna o total de horas requeridas no formato 'HH:MM'.
     */
    private function getHoursRequired(string $dateStart, string $dateEnd) : string

    {
        // Obtém o ano da data de início
        $year = substr($dateStart, 0, 4);

        // Obtém a lista de feriados do ano


        $holidays = $this->getHolidays($year);


        // Cria um objeto Carbon a partir da data de início
        $dateCurrent = Carbon::createFromDate($dateStart) ;

        // Inicializa o contador de dias úteis
        $numberOfDays = 0;


        // Loop até a data atual ser menor ou igual à data de término
        while ($dateCurrent->lessThanOrEqualTo(Carbon::createFromDate($dateEnd)))
       {

           // Verifica se o dia atual não é final de semana e não é um feriado
            if (!$dateCurrent->isWeekend() && !in_array($dateCurrent->format('Y-m-d') , $holidays))
            {
                $numberOfDays ++;

            }

           // Avança para o próximo dia
            $dateCurrent->addDay();
       }

        // Calcula o número total de horas
       $numberOfHors = $numberOfDays * 8;

        // Divide as horas em horas e minutos
        $hours = floor($numberOfHors);
        $minutes = ($numberOfHors - $hours) * 60;

        // Formata o resultado como 'HH:MM'
        $stringFormated = sprintf("%02d:%02d",$hours, $minutes);

        return $stringFormated;

    }

    public function filtro(Request $request)
    {
        return view('folha-pontos');
    }
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFolhaPontosRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(FolhaPontos $folhaPontos)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FolhaPontos $folhaPontos)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFolhaPontosRequest $request, FolhaPontos $folhaPontos)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FolhaPontos $folhaPontos)
    {
        //
    }


}
