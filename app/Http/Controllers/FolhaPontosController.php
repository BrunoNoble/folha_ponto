<?php

namespace App\Http\Controllers;

use App\Models\FolhaPontos;
use App\Http\Requests\StoreFolhaPontosRequest;
use App\Http\Requests\UpdateFolhaPontosRequest;
use Carbon\Carbon;
use DateTime;
use GuzzleHttp\Client;
use http\Env\Request;
use function Sodium\add;

class FolhaPontosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {


        $itens = FolhaPontos::all();
        $hoursOfMonth = $this->getHoursOfDate('month');
        $hoursOfWeek = $this->getHoursOfDate('week');
        $hoursOfLastWeek = $this->getHoursOfDate('lastweek');

        $hoursOfLasMonth =  $this->getHoursOfDate('lastmonth');
        $totalHours = $this->getHoursOfDate('year');

        $infos = [['Esta Semana',$hoursOfWeek],
            ['Última Semana',$hoursOfLastWeek],
            ['Este Mês',$hoursOfMonth],
            ['Último Mês',$hoursOfLasMonth],
            ['Total', $totalHours]
            ];

        return view('folha-pontos',[
            'itens'=>$itens,
            'hoursinfo' => $infos

        ]);
    }



    /**
     * Obetem o total de horas da folha de ponto baseado na semana atual.
     * Retorna a string formatada do total de horas
     */
    private function getHoursOfDate(string $conditon ): array
    {
        switch ($conditon)
        {
            case 'week':
                //Pegando data atual
                $dataAtual = Carbon::now();


                //baseado na data atual pega comeco da semana
                $startDate = $dataAtual->startOfWeek()->format('Y-m-d');
                //baseado na data atual pega ultimo dia da semana
                $endDate = $dataAtual->endOfWeek()->format('Y-m-d');

                break;
            case 'lastweek':
                //Pegando data atual
                $dataAtual = Carbon::now()->subWeek();

                //baseado na data atual pega comeco da semana
                $startDate = $dataAtual->startOfWeek()->format('Y-m-d');
                //baseado na data atual pega ultimo dia da semana
                $endDate = $dataAtual->endOfWeek()->format('Y-m-d');
                break;
            case 'month':
                //Pegando data atual
                $dataAtual = Carbon::now();

                $startDate = $dataAtual->firstOfMonth()->format('Y-m-d');
                //baseado na data atual pega ultimo dia da semana
                $endDate = $dataAtual->lastOfMonth()->format('Y-m-d');


                break;
            case 'lastmonth':
                //Pegando data atual
                $dataAtual = Carbon::now()->lastOfMonth();

                $startDate = $dataAtual->firstOfMonth()->format('Y-m-d');

                //baseado na data atual pega ultimo dia da semana
                $endDate = $dataAtual->lastOfMonth()->format('Y-m-d');

                break;
            case 'year':
                //Pegando data atual
                $dataAtual = Carbon::now();

                $startDate = $dataAtual->firstOfYear()->format('Y-m-d');
                //baseado na data atual pega ultimo dia da semana
                $endDate = $dataAtual->lastOfYear()->format('Y-m-d');

                break;
        }

        $hoursRequired = $this->getHoursRequired($startDate,$endDate);


        //filtro a db baseado na semana
        $week = FolhaPontos::whereBetween('entry_date', [$startDate, $endDate])->get();

        //declaracao variavel
        $minutesOfWeek = 0;

        //percore os dados da semana
        foreach ($week as $day)
        {
            //obtem a horas feitas no dia
            $stringHoursOfDay = $day->getHours();

            //separa as horas e minutos
            list($hour, $minutes) = explode(':', $stringHoursOfDay);

            //transforma hora em minutos e soma aos minutos
            $totalMinutes = $hour * 60 + $minutes;

            //incrementa a variavel
            $minutesOfWeek += $totalMinutes;

        }
        //convertendo minutos em horas
        $hours = floor($minutesOfWeek / 60);
        //obetend apenas minutos
        $minutes = $minutesOfWeek % 60;

        //formatando a string
        $formatedHours = sprintf( "%02d:%02d",$hours, $minutes);

        $data = [$formatedHours, $hoursRequired ];
        return $data;
    }

    /**
     * Puxa de uma api todos os feriados do ano
     * @return array
     */
    private function getHolidays() : array
    {
        // Definindo a chave de API (substitua 'YOUR_API_KEY' pela sua chave real)
        $apiKey = 'YOUR_API_KEY';

        // Definindo o código do país para Portugal
        $countryCode = 'PT';

        // Criando uma nova instância do cliente Guzzle HTTP
        $client = new Client();

        // Fazendo uma requisição GET para a API de feriados
        $response = $client->get("https://date.nager.at/Api/v2/PublicHolidays/2023/{$countryCode}?apikey={$apiKey}");

        // Decodificando a resposta JSON para um array associativo
        $holidays = json_decode($response->getBody(), true);

        $holidaysDates = [];

        foreach ($holidays as $key => $day)
        {

            $holidaysDates[] = $day['date'];


        }
        // Retornando a view 'holidays' com os feriados compactados para serem usados na view
        return $holidaysDates;

    }

    private function getHoursRequired(string $dateStart, string $dateEnd) : int

    {
        $holidays = $this->getHolidays();

        $dateCurrent = Carbon::createFromDate($dateStart) ;


        $numberOfDays = 0;

       while ($dateCurrent->lessThan(Carbon::createFromDate($dateEnd)))
       {
            if (!$dateCurrent->isWeekend() && !in_array($dateCurrent->format('Y-m-d') , $holidays))
            {
                $numberOfDays ++;
            }

            $dateCurrent->addDay();
       }



       return $numberOfDays * 8;

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
