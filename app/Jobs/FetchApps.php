<?php

namespace App\Jobs;

use App\Models\Record;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FetchApps implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $appids;
    public $proxy;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($appids, $proxy)
    {
        $this->appids = $appids;
        $this->proxy = $proxy;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Client $client)
    {
        try {
            $json = json_decode($client->get('http://store.steampowered.com/api/appdetails/', [
                'query' => [
                    'appids' => $this->appids,
                    'cc' => config('steam.country'),
                    'l' => config('steam.language'),
                    'v' => 1,
                    'filters' => 'price_overview',
                ],
                'proxy' => $this->proxy,
                'connect_timeout' => 5,
                'read_timeout' => 5,
            ])->getBody(), true);

            $results = collect($json);

            $original = 0;
            $sale = 0;
            foreach ($results as $result) {
                if (!isset($result['data']['price_overview'])) {
                    continue;
                }

                $original += $result['data']['price_overview']['initial'] / 100;
                $sale += $result['data']['price_overview']['final'] / 100;
            }

            Record::where('cc', config('steam.country'))
                ->where('language', config('steam.language'))
                ->where('created_at', '>=', today())
                ->increment('original', $original);

            Record::where('cc', config('steam.country'))
                ->where('language', config('steam.language'))
                ->where('created_at', '>=', today())
                ->increment('sale', $sale);

            Cache::pull('view');

            usleep(500000);

        } catch (\Exception $e) {

            Log::error('Proxy error?', ['message' => $e]);
            $proxies = [];

            foreach(range(3001, 3021) as $port) {
                $proxies[] = config('steam.proxy1').$port;
            }

            foreach(range(30011, 30045) as $port) {
                $proxies[] = config('steam.proxy2').$port;
            }

            $proxy = $proxies[array_rand($proxies)];

            dispatch(new FetchApps($this->appids, $proxy));
        }
    }
}
