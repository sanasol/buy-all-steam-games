<?php

namespace App\Console\Commands;

use App\Jobs\FetchApps;
use App\Models\Record;
use ArrayIterator;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use InfiniteIterator;
use LimitIterator;

class Fetch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fetches amount of prices of all Steam games';

    /**
     * Execute the console command.
     *
     * @param Client $client
     *
     * @return void
     */
    public function handle(Client $client)
    {
        Record::where('cc', config('steam.country'))
            ->where('language', config('steam.language'))
            ->where('created_at', '>=', today())
            ->delete();

        Record::create([
            'original' => 0,
            'sale'     => 0,
            'cc'       => config('steam.country'),
            'language' => config('steam.language'),
        ]);

        $json = json_decode($client->get('http://api.steampowered.com/ISteamApps/GetAppList/v2')->getBody(), true);
        $lists = collect($json['applist']['apps'])->pluck('appid');
        $chunks = $lists->chunk(config('steam.chunk_size'));

        $this->info('fetching... '.$chunks->count());
        $progressBar = $this->output->createProgressBar($chunks->count());

        $proxies = [];

        foreach(range(3001, 3021) as $port) {
            $proxies[] = config('steam.proxy1').$port;
        }

        foreach(range(30011, 30045) as $port) {
            $proxies[] = config('steam.proxy2').$port;
        }

        /** @var Collection $chunk */
        foreach ($chunks as $i => $chunk) {
            $appids = $chunk->implode(',');

            $subset = iterator_to_array(
                new LimitIterator(
                    new InfiniteIterator(
                        new ArrayIterator($proxies)
                    ),
                    $i,
                    1
                )
            );

            $proxy = array_values($subset)[0];

            dispatch(new FetchApps($appids, $proxy));
            $progressBar->advance();
        }
    }

    /**
     * Store fetched prices.
     *
     * @param $original number
     * @param $sale number
     */
    public function store($original, $sale)
    {
        Record::create([
            'original' => $original / 100,
            'sale'     => $sale / 100,
            'cc'       => config('steam.country'),
            'language' => config('steam.language'),
        ]);

        Cache::pull('view');
    }
}
