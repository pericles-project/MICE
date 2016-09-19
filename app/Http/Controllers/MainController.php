<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use GuzzleHttp\Client;

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
        return redirect()->route('main');
    }

    /**
     * Displays change impact
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $allParams = array('uuid', 'repository_name', 'change', 'callback_url');
        $params = $request->session()->get('params');
        $results = array();

        if (isset($params) == true) {
            foreach($allParams as $paramName) {
                if (array_key_exists($paramName, $params) == true && empty($params[$paramName]) == true) {
                  return response()->view('errors.400', ['message' => "Required parameter {$paramName} is missing."], 400);
                }
            }

            // Get trees from script
            $results = $this->getDependencyTrees($request);
            $results = json_decode($results, true);
        } else {
            $case = Input::get("case");

            if (isset($case) == true) {
                // Get trees from json file
                // $results = file_get_contents('data' . $case . '.json');
                $results = file_get_contents('new_video_delta_RESULTS.json');
                $results = json_decode($results, true);
            }
        }

        if (empty($results) == false) {
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

            $request->session()->put('results', $results);
            return view('home', ['results' => $results, 'params' => $params]);
        } else {
            return view('intro');
        }
    }

    /**
     * Gets the dependency trees
     *
     * @return string $results The dependency trees in json format
     */
    public function getDependencyTrees(Request $request)
    {
        $params = $request->session()->get('params');

        $client = new Client();

        try {
            $response = $client->request('POST', 'http://127.0.0.1:5000/', array(
                'form_params' => array(
                    'repository_name' => $params['repository_name'],
                    'change' => $params['change'])
                )
            );
        }
        catch (GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            throw new \Exception("Error executing command - error code:" . $responseBodyAsString);
        }

        $results = $response->getBody();

        return $results;
    }

    /**
     * Gets the dependency graph for a specific delta statement
     *
     * @params int $index Index of delta statement
     * @return string Response Graph in json format
     */
    public function graph(Request $request, Response $response, $index = null)
    {
        $results = $request->session()->get('results');
        $index = isset($index) == true ? $index : count($results['statements']) - 1;
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
