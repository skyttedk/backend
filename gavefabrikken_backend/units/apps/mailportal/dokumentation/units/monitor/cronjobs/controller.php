<?php

namespace GFUnit\monitor\cronjobs;
use GFBiz\units\UnitController;
use GFUnit\navision\syncprivatedelivery\ErrorCodes;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function index()
    {

        $this->view("dashboard");



    }


    public function loadjobs()
    {

       $sql = "SELECT * FROM ( SELECT *, ROW_NUMBER() OVER(PARTITION BY jobname ORDER BY created DESC) as rn FROM cronlog WHERE created >= NOW() - INTERVAL 7 DAY ) AS ranked WHERE rn <= 5 order by jobname ASC, created DESC;";
        $cronData = \CronLog::find_by_sql($sql);

        $groupedData = [];

        // Gruppér data efter jobname
        foreach ($cronData as $data) {

            $jobname = $data->jobname;
            if (!isset($groupedData[$jobname])) {
                $groupedData[$jobname] = [
                    'joblist' => [],
                    'totalRuntime' => 0,
                    'totalInterval' => 0,
                    'previousCreated' => null
                ];
            }

            // Tilføj data til joblist
            $groupedData[$jobname]['joblist'][] = array(
                "id" => $data->id,
                "jobname" => $data->jobname,
                "created" => $data->created->format('Y-m-d H:i:s'),
                "runtime" => $data->runtime,
                "status" => $data->status,
                "message" => $data->message,
                "error" => $data->error,
                "statsjson" => $data->statsjson,
                "debugdata" => $data->debugdata,
                "output" => $data->output,
                "url" => $data->url,
            );

            // Opdater total runtime
            $groupedData[$jobname]['totalRuntime'] += $data->runtime;

            // Beregn interval mellem kørsler
            if ($groupedData[$jobname]['previousCreated']) {
                $currentCreated = $data->created;
                $diff = $currentCreated->diff($groupedData[$jobname]['previousCreated']);
                $interval = $diff->days * 24 * 60; // Tilføj dage konverteret til minutter
                $interval += $diff->h * 60; // Tilføj timer konverteret til minutter
                $interval += $diff->i; // Tilføj minutter

                $groupedData[$jobname]['totalInterval'] += $interval;
            }


            // Opdater previousCreated for næste iteration
            $groupedData[$jobname]['previousCreated'] = $data->created;
        }

        // Beregn og tilføj de ønskede data til hvert job
        $result = [];
        foreach ($groupedData as $jobname => $data) {

            $count = count($data['joblist']);
            $averageRuntime = $count ? $data['totalRuntime'] / $count : 0;
            $averageInterval = ($count - 1) > 0 ? $data['totalInterval'] / ($count - 1) : 0;
            $lastCreated = $data['joblist'][0]['created'];

            // Beregn gennemsnitlig interval i hele minutter og sekunder
            $averageIntervalMinutes = floor($averageInterval);
            $averageIntervalSeconds = floor(($averageInterval - $averageIntervalMinutes) * 60);

            // Opret DateInterval
            $intervalSpec = 'PT' . $averageIntervalMinutes . 'M' . $averageIntervalSeconds . 'S';
            $interval = new \DateInterval($intervalSpec);

            // Beregn næste kørsel baseret på seneste kørsel plus det gennemsnitlige interval
            $nextRun = (new \DateTime($lastCreated))->add($interval);
            $missedNextRun = (new \DateTime() > $nextRun->add(new \DateInterval('PT1H')));


            $result[] = [
                'jobname' => $jobname,
                'runs' => $count,
                'averageRuntime' => $averageRuntime,
                'averageInterval' => $averageInterval,
                'totalInterval' => $data['totalInterval'],
                'nextRun' => $nextRun->format('Y-m-d H:i:s'),
                'missedNextRun' => $missedNextRun,
                'latestStatus' => $data['joblist'][0]['status'],
                'joblist' => $data['joblist']
            ];

        }

        echo json_encode($result);

    }


}