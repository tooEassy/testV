<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;
use App\Models\State;
use App\Models\SdnItem;

class MainController extends Controller
{
    public function update() {
        State::whereInfo('sdn-update')->update(['run' => 1]);

        $xml = $this->getXmlContent();

        if ($xml) {
            SdnItem::upsert($xml, ['uid'], ['first_name', 'last_name']);
            State::whereInfo('sdn-update')->update(['run' => 0]);

            return response()->json([
                'result' => true,
                'info' => '',
                'code' => 200,
            ]);
        } else {
            State::whereInfo('sdn-update')->update(['run' => 0]);

            return response()->json([
                'result' => false,
                'info' => 'service unavailable',
                'code' => 503,
            ]);
        }
    }

    private function getXmlContent()
    {
        $xml = file_get_contents(env('SDN_URL'));

        if ($xml === false) {
            return false;
        }

        $xmlObject = simplexml_load_string($xml);
        if ($xmlObject === false) {
            return false;
        }

        $data = [];
        foreach ($xmlObject->sdnEntry as $sdnEntry) {
            if ($sdnEntry->sdnType == 'Individual') {
                $data[] = [
                    'uid' => $sdnEntry->uid,
                    'first_name' => $sdnEntry->firstName,
                    'last_name' => $sdnEntry->lastName,
                ];
            }
        }
        return $data;
    }

    public function state() {
        $state = State::whereInfo('sdn-update')->first();
        if (!$state) {
            return response()->json([
                'result' => false,
                'info' => 'empty'
            ]);
        }

        if ($state->run) {
            return response()->json([
                'result' => false,
                'info' => 'updating'
            ]);
        } else {
            return response()->json([
                'result' => true,
                'info' => 'ok'
            ]);
        }
    }

    public function getNames(Request $request) {
        if (isset($request->type) && (strtolower($request->type) === 'strong')) {
            $sdnTypes = SdnItem::select('uid', 'first_name', 'last_name')
                ->where(function ($query) use ($request) {
                    $query->whereRaw("CONCAT(first_name, ' ', last_name) = ?", [$request->name]);
                })
                ->get();
        } else {
            $sdnTypes = SdnItem::select('uid', 'first_name', 'last_name')
                ->where(function ($query) use ($request) {
                    $query->where('first_name', 'like', '%' . $request->name . '%')
                        ->orWhere('last_name', 'like', '%' . $request->name . '%');
                })
                ->get();
        }

        return response()->json($sdnTypes);
    }
}
