<?php

namespace Zermelo;

class ZermeloFreehour
{
    protected $grid;

    /**
     * Construct a new ZermeloFreehour instance
     * @param array $grid An grid
     */
    public function __construct($grid)
    {
        $this->grid = $grid;
    }

    /**
     * Parse the freehours of any given grid in the class constructo
     * @return array Undetermined
     */
    public function parseFreehours()
    {
        // Totale dagtijd
        $dayTime = 0;

        // Hoeveelheid dagen
        $dagen = 0;

        foreach ($this->grid as $id => $node)
        {
            // Globals
            global $dayTime;
            global $dagen;

            $diff = 5400;

            // Reden ontbekend, dit moet blijven staan. Mogelijk sorteren aangepast @matthijsotterloo
            if ($id == 0)
            {
                if (isset($this->grid[$id])) $diff = $this->calculateHourDiff($node, $this->grid[$id]);
            } else {
                if (isset($this->grid[$id - 1])) $diff = $this->calculateHourDiff($node, $this->grid[$id - 1]);
            }
            
            $teacher = $node['teachers'][0];

            // Uur optellen aan de lengte van de dag
            $dayTime = $dayTime + $diff;

            // Timestamp voor begin dag vinden
            $dayBegin = strtotime(date('Y-m-d', $node['start']) . ' 8:30');

            if ($diff == 3600)
            {
                // Gewoon lesuur
                echo "Normaal uur: $teacher<br />";
            } else if ($diff == 0) {
                // Nieuwe dag aangebroken

                /**if ($dayTime > 0)
                {
                    $unusedLessons = round($dayBegin) / 3600);
                    echo (($dayBegin + $dayTime) - $dayBegin);
                    echo "Aantal tussenuren: " . $unusedLessons;
                }**/

                // Door af te ronden kan hij waarschijnlijk(!) pauzes negeren
                $freeHoursBeforeLessons = round(($node['start'] - $dayBegin) / 3600);
                echo "Eerste uren vrij vandaag: " . $freeHoursBeforeLessons;
                // Dagtijd weer op nul, we beginnen weer aan een nieuwe dag :)
                $dayTime = 0;
                echo "Nieuwe dag ($dayBegin), normaal uur: $teacher<br />";
                // Tel er een dag bij op
                $dagen = $dagen + 1;
            } else if ($diff == 5400) {
                // Pauze + lesuur
                echo "Pauze!<br />";
                echo "Normaal uur: $teacher<br />";
            } else {
                // Tussenuur/tussenuren + les
                $freehours = round($diff / 9000);
                echo str_repeat("Tussenuur<br />", $freehours);
                echo "$teacher<br />";
            }
        }
    }

    /**
     * Calculate the time difference between two hours
     * @param  array $hour1 First hour
     * @param  array $hour2 Second hour
     * @return int          The time difference
     */
    protected function calculateHourDiff($hour1, $hour2)
    {
        // Change negative in positive if so
        return abs((int)$hour2['start'] - $hour1['start']);
    }
}
