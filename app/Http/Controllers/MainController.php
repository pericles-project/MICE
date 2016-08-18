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
     * Initializes app
     *
     * @return void
     */
    public function init(Request $request)
    {
        $params = array(
            'uuid' => Input::get("uuid"),
            'repository_name' => Input::get("repository_name"),
            'change' => Input::get("change"),
            'callback_url' => Input::get("callback_url") ? : $request->server('HTTP_REFERER')
        );
        $params['callback_url'] = strstr('?', $params['callback_url']) ? '&amp;' : '?' . 'uuid=' . $params['uuid'];

        $request->session()->put('params', $params);
        return redirect()->route('main', ['uuid' => $params['uuid']]);
    }

    /**
     * Displays change impact
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $params = $request->session()->get('params');

        if (isset($params) == true) {
            foreach($params as $paramName => $paramValue) {
                if (empty($paramValue)) {
                  return response()->view('errors.400', ['message' => "Required parameter {$paramName} is missing."], 400);
                }
            }

            // Store deltas in json file
            // TODO remove this
            file_put_contents(base_path() . '\\libraries\\script\\deltas\\' . $params['uuid'] . '.n3', $params['change']);

            // Get trees from script
            $results = $this->getDependencyTrees($request);
            $results = json_decode($results[0], true);
        } else {
            // if (!$params['uuid']) {
            //     $case = Input::get("case") ? : 1;
            //     $request->session()->put('case', $case);
            // }

            // Get trees from json file
            $results = file_get_contents('new_video_delta_RESULTS.json');
            $results = json_decode($results, true);
        }

        // Merge deletions and insertions into one array
        $results['statements'] = array_merge($results['deletions'], $results['insertions']);
        unset($results['deletions']);
        unset($results['insertions']);

        foreach($results['statements'] as $key => &$row) {
            $uris = array('subject', 'predicate', 'object');
            // TODO remove this when it's added to the json
            if (array_key_exists('subject', $row) == false) {
                $row['subject'] = $results['subject'];
            }
            foreach($uris as $uri) {
              if (array_key_exists($uri, $row)) {
                $row[$uri] = getResourceNameFromURI($row[$uri]);
              }
            }
            if (empty($row['name']) == true) {
                $row['name'] = $row['subject'];
            }
        }

        // var_dump($results);
        $results['statements'] = $this->calculateStatistics($results['statements']);
        $results = $this->calculateTotalStatistics($results);

        // var_dump($results);
        $request->session()->put('results', $results);

        return view('home', ['results' => $results, 'params' => $params]);
    }

    /**
     * Gets the dependency trees
     *
     * @return string $results The dependency trees in json format
     */
    public function getDependencyTrees(Request $request)
    {
        $params = $request->session()->get('params');

        // TODO pass params to the script
        $scriptPath = addcslashes(base_path() . "\\libraries\\script\\", "\\");
        $command = "C:\Python27\python.exe {$scriptPath}CreateDependencyTreesForDelta.py {$scriptPath}";
        exec($command, $results, $return);

        if ($return) {
            throw new \Exception("Error executing command - error code:" . $return);
        }

        return $results;
    }

    /**
     * Gets the dependency graph for a specific delta statement
     *
     * @params int $index Index of delta statement
     * @return string Response Graph in json format
     */
    public function graph(Request $request, Response $response, $index = 0)
    {
        $results = $request->session()->get('results');
        $graph = $results['statements'][$index];
        return response()->json($graph);
    }

    /**
     * Calculates statistics from dependency trees
     *
     * @params array $dependencyTreesResults Dependency trees
     * @return array $dependencyTreesResults Dependency trees containing statistics eg total, impacted, not impacted resources
     */
    public function calculateStatistics($dependencyTreesResults)
    {
        foreach($dependencyTreesResults as &$node) {

            $node['statistics'] = array(
                'total' => 0,
                'impacted' => 0,
                'not_impacted' => 0
            );

            $childNodes = flatten(array($node), 0);

            foreach($childNodes as $childNode) {
                if (isset($childNode['impacted']) == true) {
                    $node['statistics']['total']++;
                    if ($childNode['impacted'] == true) {
                      $node['statistics']['impacted']++;
                    } else {
                      $node['statistics']['not_impacted']++;
                    }
                }
            }
        }

        return $dependencyTreesResults;
    }

    /**
     * Calculates the total impact statistics for all statements
     *
     * @params array $results Dependency trees
     * @return array $results Dependency trees containing total impact statistics
     */
    public function calculateTotalStatistics($results)
    {
        $totalImpact = 0;

        // Group statements by predicate
        $statements = groupByKey($results['statements'], 'predicate');

        foreach($statements as $predicate => $statements) {
            if (isset($statements) == true) {
                if (count($statements) == 1) {
                    $keyToCheck = current((array_keys($statements)));
                    if ($statements[$keyToCheck]['statistics']['impacted'] == true)
                        $totalImpact += $statements[$keyToCheck]['statistics']['impacted'];
                } else if (count($statements) > 1) {
                    foreach($statements as $statement) {
                        if ($statement['action'] == 'insertion' && $statement['statistics']['impacted'] == true) {
                            $totalImpact += $statement['statistics']['impacted'];
                            break;
                        }
                      }
                }
            }
        }

        $results['totalImpact'] = $totalImpact;

        return $results;
    }
}
