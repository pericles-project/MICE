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
            'ERMR_repository' => Input::get("ERMR_repository"),
            'delta_stream' => Input::get("delta_stream"),
            'callback_url' => Input::get("callback_url")
        );

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
        $requiredParams = array('ERMR_repository', 'delta_stream');
        $params = $request->session()->get('params');
        $results = array();
        $case = Input::get("case");
        $action = Input::get("action");
        $updateResponse = null;

        if (isset($case) == true) {
            // Get trees from json file
            $results = file_get_contents('data' . $case . '.json');
            $results = json_decode($results, true);
        } else if (isset($action) == true) {
          if ($action == 'accept') {
            $updateResponse = $this->updateRepository($request);
            $updateResponse = json_decode($updateResponse, true);
          }

          if (empty($params['callback_url']) == false) {
            header('Location:' . $params['callback_url'] . '?action=' . $action . '&success=1');
            exit;
          }
        }

        if (isset($params) == true) {
            foreach($requiredParams as $paramName) {
                if (array_key_exists($paramName, $params) == false || empty($params[$paramName]) == true) {
                  return response()->view('errors.400', ['params' => array(), 'message' => "Required parameter {$paramName} is missing."], 400);
                }
            }

            // Get trees from script
            $results = $this->getDependencyTrees($request);
            $results = json_decode($results, true);
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
            return view('home', ['results' => $results, 'params' => $params, 'action' => $action, 'updateResponse' => $updateResponse]);
        } else {
            return view('intro', ['params' => $params]);
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
            $response = $client->request('POST', env('API_DEPENDENCY_GRAPH_URL'), array(
                'json' => array(
                    'ERMR_repository' => $params['ERMR_repository'],
                    'delta_stream' => $params['delta_stream'])
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
     * Updates the ERMR repository
     *
     * @return string $r Response
     */
    public function updateRepository(Request $request)
    {
        $params = $request->session()->get('params');

        $client = new Client();

        try {
            $response = $client->request('POST', env('API_UPDATE_URL'), array(
                'json' => array(
                    'ERMR_repository' => $params['ERMR_repository'],
                    'delta_stream' => $params['delta_stream'])
                )
            );
        }
        catch (GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            throw new \Exception("Error executing command - error code:" . $responseBodyAsString);
        }

        $r = $response->getBody();

        return $r;
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
