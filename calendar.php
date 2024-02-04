<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/themes/base/jquery-ui.css" rel="stylesheet" type="text/css" />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/jquery-ui.min.js"></script>

<?php
class Calendar
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->naviHref = htmlentities($_SERVER['PHP_SELF']);
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
            $this->url = "https://";
        else
            $this->url = "http://";
        $this->url .= $_SERVER['HTTP_HOST'];

        // $this->url .= $_SERVER['REQUEST_URI'];
    }
    /********************* PROPERTY ********************/
    private $dayLabels = array("จันทร์", "อังคาร", "พุธ", "พฤหัสบดี", "ศุกร์", "เสาร์", "อาทิตย์");
    private $currentYear = 0;
    private $currentMonth = 0;
    private $currentDay = 0;
    private $currentDate = null;
    private $daysInMonth = 0;
    private $naviHref = null;
    private $url = null;

    /********************* PUBLIC **********************/

    /**
     * print out the calendar
     */
    public function show()
    {
        $year  = null;
        $month = null;

        if (null == $year && isset($_GET['year'])) {
            $year = $_GET['year'];
        } else if (null == $year) {
            $year = date("Y", time());
        }

        if (null == $month && isset($_GET['month'])) {
            $month = $_GET['month'];
        } else if (null == $month) {
            $month = date("m", time());
        }

        $this->currentYear = $year;
        $this->currentMonth = $month;
        $this->daysInMonth = $this->_daysInMonth($month, $year);

        $content = '<div id="calendar">' .
            '<div class="calendar-header">' .
            $this->_createNavigationHeader() .
            '</div>' .
            '<div class="calendar-content">' .
            '<div class="day-label">' . $this->_createDayOfWeekLabels() . '</div>';
        $content .= '<div class="clear"></div>';
        $content .= '<div class="dates">';
        $weeksInMonth = $this->_weeksInMonth($month, $year);
        // Create weeks in a month
        for ($i = 0; $i < $weeksInMonth; $i++) {
            //Create days in a week
            for ($j = 1; $j <= 7; $j++) {
                $content .= $this->_showDay($i * 7 + $j);
            }
        }
        $content .= '</div>';
        $content .= '<div class="clear"></div>';
        $content .= '</div>';
        $content .= '</div>';
        return $content;
    }

    /********************* PRIVATE **********************/
    /**
     * create the li element for ul
     */
    private function _showDay($cellNumber)
    {

        if ($this->currentDay == 0) {
            $firstDayOfTheWeek = date('N', strtotime($this->currentYear . '-' . $this->currentMonth . '-01'));
            if (intval($cellNumber) == intval($firstDayOfTheWeek)) {
                $this->currentDay = 1;
            }
        }

        if (($this->currentDay != 0) && ($this->currentDay <= $this->daysInMonth)) {
            $this->currentDate = date('Y-m-d', strtotime($this->currentYear . '-' . $this->currentMonth . '-' . ($this->currentDay)));
            $display_date = $this->currentDay;
            $this->currentDay++;
        } else {
            $this->currentDate = null;
            $display_date = null;
        }

        $today_class = ' ';
        $today_date = date('Y-m-d');
        if ($today_date == $this->currentDate) {
            $today_class = ' today ';
        }

        $bg_class = ' weekday ';
        if ($cellNumber % 7 == 6 || $cellNumber % 7 == 0) {
            $bg_class = ' weekend ';
        }

        // TODO: get events
        $events = array(
            array('title' => 'event title', 'start-time' => '2024-02-25 13:00:00', 'end-time' => '2024-02-25 16:00:00'),
            array('title' => 'event title 2', 'start-time' => '2024-02-02 13:00:00', 'end-time' => '2024-02-02 16:00:00'),
            array('title' => 'event title 3', 'start-time' => '2024-01-25 13:00:00', 'end-time' => '2024-01-25 16:00:00'),
            array('title' => 'event title 4', 'start-time' => '2024-02-05 13:00:00', 'end-time' => '2024-02-05 16:00:00'),
            array('title' => 'event title 5', 'start-time' => '2024-02-05 08:00:00', 'end-time' => '2024-02-05 16:00:00'),
            array('title' => 'event title 6', 'start-time' => '2024-02-05 13:00:00', 'end-time' => '2024-02-05 20:00:00')
        );

        $filtered_events = [];
        foreach ($events as $event) {
            if (!$this->currentDate) {
                return false;
            }
            $today_date = (new DateTime($this->currentDate))->setTime(12, 0, 0);
            $start_date = (new DateTime($event['start-time']))->setTime(12, 0, 0);
            $end_date = (new DateTime($event['end-time']))->setTime(12, 0, 0);

            if ($start_date <= $today_date && $end_date >= $today_date) {
                $filtered_events[] = $event;
            }
        };

        $render_events = '';

        foreach ($filtered_events as $index => $event) {
            if ($index >= 2) {
                $render_events .= '<p class="event-title">' . count($filtered_events) - $index . ' more...</p>';
                break;
            }

            $render_events .= '<p class="event-title">' . $event['title'] . '</p>';
        }



        return '<div id="li-' . $this->currentDate . '" class="date-block' . $bg_class . ($cellNumber % 7 == 1 ? ' start ' : ($cellNumber % 7 == 0 ? ' end ' : ' ')) .
            ($display_date == null ? 'mask' : '') . $today_class . '">' .
            '<p class="calendar-date">' .
            $display_date .
            '</p>' .
            '<div class="scheduled-events">' .
            $render_events .
            '</div>' .
            '</div>';
    }

    /**
     * create navigation
     */
    private function _createNavigationHeader()
    {

        $nextMonth = $this->currentMonth == 12 ? 1 : intval($this->currentMonth) + 1;
        $nextYear = $this->currentMonth == 12 ? intval($this->currentYear) + 1 : $this->currentYear;
        $preMonth = $this->currentMonth == 1 ? 12 : intval($this->currentMonth) - 1;
        $preYear = $this->currentMonth == 1 ? intval($this->currentYear) - 1 : $this->currentYear;

        $be_year = $this->currentYear + 543;

        $prev_href = $this->url . $this->naviHref . '?month=' . sprintf('%02d', $preMonth) . '&year=' . $preYear;
        $next_href = $this->url . $this->naviHref . '?month=' . sprintf("%02d", $nextMonth) . '&year=' . $nextYear;

        return
            '<nav class="nav-header">' .
            '<h1>' . date('Y F', strtotime($be_year . '-' . $this->currentMonth . '-1')) . '</h1>' .
            '<div role="button" class="prev-month" onclick="doAjax(\'' .  sprintf('%s', $prev_href) . '\')"><</div>' .
            '<div role="button" class="next-month" onclick="doAjax(\'' . sprintf('%s', $next_href)  . '\')">></div>' .
            '</nav>';
    }

    /**
     * create calendar week labels
     */
    private function _createDayOfWeekLabels()
    {
        $content = '';
        foreach ($this->dayLabels as $index => $label) {
            $content .= '<div class="' . ($label == 6 ? 'end title' : 'start title') . ' title">' . $label . '</div>';
        }
        return $content;
    }

    /**
     * calculate number of weeks in a particular month
     */
    private function _weeksInMonth($month = null, $year = null)
    {
        if (null == ($year)) {
            $year =  date("Y", time());
        }

        if (null == ($month)) {
            $month = date("m", time());
        }

        // find number of days in this month
        $daysInMonths = $this->_daysInMonth($month, $year);
        $numOfweeks = ($daysInMonths % 7 == 0 ? 0 : 1) + intval($daysInMonths / 7);
        $monthEndingDay = date('N', strtotime($year . '-' . $month . '-' . $daysInMonths));
        $monthStartDay = date('N', strtotime($year . '-' . $month . '-01'));
        if ($monthEndingDay < $monthStartDay) {
            $numOfweeks++;
        }

        return $numOfweeks;
    }

    /**
     * calculate number of days in a particular month
     */
    private function _daysInMonth($month = null, $year = null)
    {

        if (null == ($year)) {
            $year =  date("Y", time());
        }

        if (null == ($month)) {
            $month = date("m", time());
        }

        return date('t', strtotime($year . '-' . $month . '-01'));
    }
}
?>
<script type="text/javascript">
    function doAjax(url) {
        // console.log('link', url)
        // $(document).ready(function() {
        //     $.ajax({
        //         url: url,
        //         type: 'GET',
        //         async: false,
        //         success: function(data) {
        //             if (data) {
        //                 console.log(data)
        //             }
        //         },
        //         else: function(data) {
        //             console.log('fucked up')
        //         }
        //     })
        // })
        $.ajax({
            url: url,
            type: 'GET',
            async: false,
            success: function(data) {
                if (data) {
                    console.log(data)
                }
            },
            else: function(data) {
                console.log('fucked up')
            }
        })
    }
</script>