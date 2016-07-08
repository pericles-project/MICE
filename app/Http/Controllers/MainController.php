<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MainController extends BaseController
{
    /**
     * Display change impact
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $params = array(
            'uuid' => Input::get("uuid"),
            'repository_name' => Input::get("repository_name"),
            'change' => Input::get("change"),
            'callback_url' => Input::get("callback_url") ? : $request->server('HTTP_REFERER')
        );
        $params['callback_url'] = strstr('?', $params['callback_url']) ? '&amp;' : '?' . 'uuid=' . $params['uuid'];

        if (!$params['uuid']) {
            $case = Input::get("case") ? : 1;
            $request->session()->put('case', $case);
        }

        // TODO validate inputs

        // Get trees from script
        // $results = $this->getDependencyTrees();
        // $results = json_decode($results[0], true);

        // Get trees from json file
        $results = file_get_contents('data_insertion.json');
        $results = json_decode($results, true);

        // Merge deletions and insertions into one array
        $results['statements'] = array_merge($results['deletions'], $results['insertions']);
        unset($results['deletions']);
        unset($results['insertions']);

        foreach($results['statements'] as &$row) {
            $uris = array('subject', 'predicate', 'object');
            // TODO remove this when it's added to the json
            if (!array_key_exists('subject', $row)) {
                $row['subject'] = $results['subject'];
            }
            foreach($uris as $uri) {
              if (array_key_exists($uri, $row)) {
                $row[$uri] = $this->getResourceNameFromURI($row[$uri]);
              }
            }
        }
        // var_dump($results);
        $this->calculateStatistics($results);

        // var_dump($results);
        $request->session()->put('results', $results);

        return view('home', ['results' => $results, 'params' => $params]);
    }

    /**
     * Get the dependency trees
     *
     * @return $results The dependency trees in json format
     */
    public function getDependencyTrees()
    {
        $scriptPath = addcslashes(base_path() . "\libraries\script\\", "\\");
        $command = "C:\Python27\python.exe {$scriptPath}CreateDependencyTreesForDelta.py {$scriptPath}";
        exec($command, $results, $return);

        if ($return) {
            throw new \Exception("Error executing command - error code:" . $return);
        }

        return $results;
    }

    /**
     * Get the dependency graph for a specific delta statement
     *
     * @params $index Index of delta statement
     * @return Response Graph in json format
     */
    public function graph(Request $request, Response $response, $index = 0)
    {
        $results = $request->session()->get('results');
        $graph = $results['statements'][$index];
        return response()->json($graph);
    }

    /**
     * Extract a resource's name from it's URI
     *
     * @params $uri The resource's URI
     * @return $name The resource's name
     */
    public function getResourceNameFromURI($uri)
    {
        $delimiter = strstr($uri, '#') ? '#' : '/';
        $tmp = explode($delimiter, $uri);
        $name = array_pop($tmp);
        return $name;
    }

    /**
     * Calculate statistics from dependency trees
     *
     * @params $dependencyTreesResults Dependency trees containing impact for individual resources
     * @return $statistics Statistics eg total, impacted, not impacted resources
     */
    public function calculateStatistics($dependencyTreesResults)
    {
        foreach($dependencyTreesResults['statements'] as &$statement) {
            $statement['statistics'] = array(
                'total' => 0,
                'impacted' => 0,
                'not_impacted' => 0
            );
            if (isset($statement['impacted']) == true) {
                $statement['statistics']['total']++;
                if ($statement['impacted'] == true) {
                  $statement['statistics']['impacted']++;
                } else {
                  $statement['statistics']['not_impacted']++;
                }
            }

            if (isset($statement['children']) == true && $statement['children']) {
                // $this->calculateStatistics($statement['children']);
            }
        }
        return $dependencyTreesResults;
    }
}
