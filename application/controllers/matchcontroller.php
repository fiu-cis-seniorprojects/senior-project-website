<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
session_start();

class Student {

    public $id;
    public $name;
    public $skills = array();
    public $scoreList = array();

    public function __construct($id, $n, $skills, $scoreList) {
        $this->id = $id;
        $this->name = $n;
        $this->skills = $skills;
        $this->scoreList = $scoreList;
        asort($this->scoreList);
    }

}

class Project {

    public $id;
    public $name;
    public $skills = array();
    public $desiredStudents = array();
    public $score;
    public $min = 1;
    public $max;

    public function __construct($id, $name, $skills, $score, $max) {
        $this->id = $id;
        $this->name = $name;
        $this->skills = $skills;
        $this->score = $score;
        $this->max = $max;
    }

    public function checkStudent(Student $studentToAdd) {
        $intersect = array_intersect($this->skills, $studentToAdd->skills);
        if (count($intersect) > 0) {
            return true;
        }
        return false;
    }

    public function addDesiredStudent(Student $studentToAdd) {
        array_push($this->desiredStudents, $studentToAdd);
    }

}

class Team {

    public $projectID;
    public $projName;
    public $score;
    public $members = array();
    public $skillsNeeded = array();
    public $studentIDs = array();

    public function __construct($projectID, $projName, $score, $skills) {
        $this->projectID = $projectID;
        $this->projName = $projName;
        $this->score = $score;
        $this->skillsNeeded = $skills;
    }

    public function addStudents($students) {
        foreach ($students as $s) {
            $this->members[$s->id] = $s;
            $this->studentIDs[$s->id] = $s->id;
            $this->skillsNeeded = array_diff($this->skillsNeeded, $s->skills);
        }
    }

    public function getStudents() {
        return $this->members;
    }

    public function checkForStudent(Student $student) {
        return in_array($student->id, $this->studentIDs);
    }

}

class TentativeMatch {

    public $projects = array();
    public $studentScore = 0;
    public $profScore = 0;

    public function __construct($projects) {
        $this->projects = $projects;
    }

}

function compare_ranks($a, $b) {
    if ($a->score == $b->score) {
        return 0;
    }
    return ($a->score < $b->score) ? -1 : 1;
}

function compare_prof_score($a, $b) {
    if ($a->profScore == $b->profScore) {
        return 0;
    }
    return ($a->profScore < $b->profScore) ? 1 : -1;
}

function array_count_values_ignore_case($array) {
    $countArr = array();
    foreach ($array as $value) {
        foreach ($countArr as $key2 => $value2) {
            if (strtolower($key2) == strtolower($value)) {
                $countArr[$key2] ++;
                continue 2;
            }
        }
        $countArr[$value] = 1;
    }
    return $countArr;
}

function checkForDups($array) {
    $subset_of_two = subsets_equal_to_n($array, 2);
    foreach ($subset_of_two as $s) {
        $values = call_user_func_array('array_intersect', $s);
        if (!empty($values))
            return true;
    }
    return false;
}

function countOnes($str) {
    return strlen(str_replace("0", "", $str));
}

function subsets_up_to_n($arr, $n) {
    $len = pow(2, count($arr));
    $subset = array();
    $a = array();
    $one = '1';
    for ($i = 1; $i < $len; $i++) {
        $bin = decbin($i);
        $a = array();
        $pos = 0;
        if (countOnes($bin) <= $n) {
            for ($j = count($arr) - strlen($bin); $j < count($arr); $j++) {
                if ($bin[$pos] == $one) {
                    array_push($a, ($arr[$j]));
                }
                $pos++;
            }
            //var_dump($a);
            array_push($subset, ($a));
        }
    }
    return $subset;
}

function subsets_equal_to_n($arr, $n) {
    $len = pow(2, count($arr));
    $subset = array();
    $a = array();
    $one = '1';
    for ($i = 0; $i < $len; $i++) {
        $bin = decbin($i);
        $a = array();
        $pos = 0;
        if (countOnes($bin) == $n) {
            for ($j = count($arr) - strlen($bin); $j < count($arr); $j++) {
                if ($bin[$pos] == $one) {
                    array_push($a, ($arr[$j]));
                }
                $pos++;
            }
            //var_dump($a);
            array_push($subset, ($a));
        }
    }
    return $subset;
}

function combinations($arr, $max) {
    $indexes = array_fill(0, count($arr), 0);//get count entries of 0's
    $tot = array();
    while (true) {
        $vm = array();
        for ($k = 0; $k < count($indexes); $k++) {
            array_push($vm, $arr[$k][$indexes[$k]]);//push [0][0] = 0? [1][0] = 0? i.e. first team for each project?
        }
        $numStudents = 0;
        $memberIDs = array();
        foreach ($vm as $team) {
            $numStudents += count($team->members);//counter number of students
            array_push($memberIDs, $team->studentIDs);//push array of id to member id's
        }
        if ($numStudents == $max) {//if it is max? huh shouldnt it always be true?
            if (!(checkForDups($memberIDs))) {//check if student in more than one team
                $tm = new TentativeMatch($vm);//mark this matching as posssible
                $profScore = 0;
                foreach ($tm->projects as $p) {//for each project in tm
                    $projectRank = $p->score;//get priority rank
                    $pScore = 0;
                    foreach ($p->members as $member) {//for each member in p
                        if (array_key_exists($p->projectID, $member->scoreList)) {//check if member want this project
                            $studentScore = $member->scoreList[$p->projectID];
                            $tm->studentScore += $studentScore;//student score of project overall
                            $pScore += $studentScore;//pscore is equal to all score of students in team for this project
                        }
                    }
                    $profScore += ($pScore * $projectRank);//count both project rank and student score for match score
                }
                $tm->profScore += $profScore;//update match's score
                if (count($tot) < 20) {//if less than 20 matchings keep pushing
                    array_push($tot, $tm);
                    usort($tot, 'compare_prof_score');//sort
                } else {
                    if ($tm->profScore > $tot[count($tot)-1]->profScore) {
                        unset($tot[count($tot)-1]);
                        array_push($tot, $tm);
                        usort($tot, 'compare_prof_score');//sort
                    }
                }
                // sort by professor score from highest score to lowest
            }
        }
        $j = count($indexes) - 1;
        while (true) {
            $indexes[$j] ++;
            if ($indexes[$j] < count($arr[$j])) {
                break;
            }
            $indexes[$j] = 0;
            $j--;
            if ($j < 0) {//exit when all matches considered?
                print_r($tot[0]);
                return $tot;
            }
        }
    }
}

class MatchController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('flash_message');
        $this->load->helper('project_summary_view_model');
        load_project_summary_models($this);
        $this->load->model('spw_match_model');
    }

    public function index() {
        $_SESSION['text'] = 'sadfa';
        if (isUserLoggedIn($this)) {
            $tempProject = new SPW_Project_Model();
            $tempUser = new SPW_User_Model();
            $user_id = getCurrentUserId($this);
            $maxNums = $this->spw_match_model->getAllProjectsMaxStudents();
            $ranks = $this->spw_match_model->getRanks($user_id);

            if (empty($ranks)) {
                $no_ranks = true;
            } else {
                $no_ranks = false;
            }

            if ($user_id) {
                $lRegularProjectsIds = $tempProject->getApprovedProjectsIds();
            }

            $lRegularProjects = $this->prepareProjectsToShow($lRegularProjectsIds);


            if ((!isset($lRegularProjects) || count($lRegularProjects) == 0)) {
                $no_results = true;
            } else {
                $no_results = false;
            }

            $data['title'] = 'Current Project';
            $data['no_results'] = $no_results;

            $projects = $lRegularProjects;
            $data['lRegularProjects'] = $lRegularProjects;
            $data['maxNums'] = $maxNums;
            $data['no_ranks'] = $no_ranks;
            $data['ranks'] = $ranks;

            $this->load->view('match_page', $data);
        }
    }

    private function getRegularProjectsForCurrentUser($lSuggProjectIds) {
        return $this->SPW_User_Model->getRegularProjectIds($lSuggProjectIds, getCurrentUserId($this));
    }

    private function prepareProjectsToShow($lProjectsIds) {
        $user_id = getCurrentUserId($this);

        if ($this->SPW_User_Model->isUserAdmin($user_id)) {
            //do nothing for now
        } else {
            //$belongProjectIdsList = $this->SPW_User_Model->userHaveProjects($user_id);
            return $this->SPW_Project_Summary_View_Model->prepareProjectsDataToShow($user_id, $lProjectsIds, NULL, FALSE);
        }
    }

    public function saveRank() {
        if (isUserLoggedIn($this)) {
            $tempProject = new SPW_Project_Model();
            $user_id = getCurrentUserId($this);
            $tempUser = new SPW_User_Model();
            $lRegularProjectsIds = $tempProject->getApprovedProjectsIds();
            $min = $this->spw_match_model->getMinimum();
        }

        $rank = array();
        foreach ($lRegularProjectsIds as $id) {
            $rank[$id] = $this->input->post($id);
        }

        foreach ($rank as $id => $r) {
            if (!is_numeric($r)) {
                $msg = "Nothing saved; non-numeric characters are disallowed.";
                setErrorFlashMessage($this, $msg);
                if ($tempUser->isUserAStudent($user_id)) {
                    redirect('home');
                } else {
                    redirect('match');
                }
            }
            if ($tempUser->isUserAStudent($user_id)) {
                if ($r == 0) {
                    $msg = "Nothing saved; zero is disallowed. Need to interest rank projects greater than 0.";
                    setErrorFlashMessage($this, $msg);
                    redirect('home');
                }
            } else {
                if ($r < 0) {
                    $msg = "Nothing saved; negatives are disallowed. Need to priority rank projects greater than -1.";
                    setErrorFlashMessage($this, $msg);
                    redirect('match');
                }
            }
        }

        $data = array(
            'user' => $user_id,
            'rank' => $rank,
        );

        if ($tempUser->isUserAStudent($user_id)) {
            $rem = 0;
            foreach ($rank as $id => $r) {
                if ($r > -1) {
                    $rem++;
                }
            }
            $this->spw_match_model->insertRanks($data);

            if ($rem < $min) {
                $need = $min - $rem;
                $msg = "Saved interest ranking information successfully, but you need to rank $min project(s) because it is the required minimum."
                        . "<br><br>You need to rank $need more project(s).";
                setErrorFlashMessage($this, $msg);
            } else {
                setFlashMessage($this, "Saved interest ranking information successfully.");
            }

            redirect('home');
        } else {
            foreach ($data['rank'] as $r) {
                if (!is_numeric($r)) {
                    $msg = "Nothing saved; non-numeric characters are disallowed.";
                    setErrorFlashMessage($this, $msg);
                    redirect('match');
                }
            }
            $this->spw_match_model->insertRanks($data);
            setFlashMessage($this, "Saved priority ranking information successfully.");
            redirect('match');
        }
    }

    public function saveMinimum() {
        $rank = htmlspecialchars($this->input->post('rank'));
        if (!is_numeric($rank)) {
            $msg = "Nothing saved; non-numeric characters are disallowed.";
        }
        if ($rank < 0) {
            $msg = "Nothing saved; negatives are disallowed.";
        }
        if (isset($msg)) {
            setErrorFlashMessage($this, $msg);
        } else {
            $this->spw_match_model->setMinimum($rank);
            setFlashMessage($this, "Saved minimum successfully.");
        }
        redirect('admin/admin_dashboard');
    }

    public function prepareProjects() {
        $approvedProjects = $this->spw_match_model->getAllApprovedProjectIDs(); //array for approved project's id numbers
        $projectsMaxStudents = $this->spw_match_model->getAllProjectsMaxStudents(); //array for all projects max student that can joined ([id] gives number for id)
        $projectRanks = $this->spw_match_model->getRanksForProjects();//array by [project id] = {rank1}? only head prof?
        $projectTitles = $this->spw_match_model->getProjectNames();//[project id] = name

        $PL = array();//project list
        foreach ($approvedProjects as $p => $id) {//have each project p identify for id p not used?
            $rank = $projectRanks[$id];//get rank
            if ($rank > 0) {//if rank non-zero
                $tempProject = new Project($id, $projectTitles[$id], $this->spw_match_model->getSkillsForProject($id), $rank, $projectsMaxStudents[$id]);//construct project
                array_push($PL, $tempProject);//push project to list, note project is made of id, name, skill array project req, head prof rank, max students that can join
            }
        }
        return $PL;
    }

    public function prepareStudents() {
        $activeStudents = $this->spw_match_model->getAllActiveStudentIDs();//arr[i] = id?
        $studentNames = $this->spw_match_model->getStudentNames();// arr[id] = fname + " " + lname

        $SL = array();
        foreach ($activeStudents as $id) {
            $tempStudent = new Student($id, $studentNames[$id], $this->spw_match_model->getSkillsForStudent($id), $this->spw_match_model->getRanksForStudent($id));
            array_push($SL, $tempStudent);
        }
        return $SL;//students are id, name, arr skills, arr of ranks [pro id] = rank
    }
    public function gotoProjectPriority() {
                $_SESSION['text'] = 'sadfa';
        if (isUserLoggedIn($this)) {
            $tempProject = new SPW_Project_Model();
            $tempUser = new SPW_User_Model();
            $user_id = getCurrentUserId($this);
            $maxNums = $this->spw_match_model->getAllProjectsMaxStudents();
            $ranks = $this->spw_match_model->getRanks($user_id);

            if (empty($ranks)) {
                $no_ranks = true;
            } else {
                $no_ranks = false;
            }

            if ($user_id) {
                $lRegularProjectsIds = $tempProject->getApprovedProjectsIds();
            }

            $lRegularProjects = $this->prepareProjectsToShow($lRegularProjectsIds);


            if ((!isset($lRegularProjects) || count($lRegularProjects) == 0)) {
                $no_results = true;
            } else {
                $no_results = false;
            }

            $data['title'] = 'Current Project';
            $data['no_results'] = $no_results;

            $projects = $lRegularProjects;
            $data['lRegularProjects'] = $lRegularProjects;
            $data['maxNums'] = $maxNums;
            $data['no_ranks'] = $no_ranks;
            $data['ranks'] = $ranks;

        
        $this->load->view('project_priority_page', $data);
        }
    }
    public function preProcessSteps() {
        // PREPARE PROJECTS
        $PL = $this->prepareProjects();

        $pskillSet = array();
        foreach ($PL as $p) {
            foreach ($p->skills as $s) {
                array_push($pskillSet, $s);//push all skills of each project to pskillList so array of array of skills
            }
        }

        // need this to get the total amount of each skill in all projects
        // ie skill name => amount of skills
        $pskillCount = array_count_values_ignore_case($pskillSet);//array[skill name] = amount of skills in it

        //PREPARE STUDENTS
        $SL = $this->prepareStudents();



        $sskillSet = array();
        foreach ($SL as $s) {
            foreach ($s->skills as $sk) {
                array_push($sskillSet, $sk);
            }
        }

        $sskillCount = array_count_values($sskillSet);//same as above for students?


        // Phase 1 Preprocessing AKA enough students/projects?
        $minTot = 0;//minimum student # needed to fufill ALL projects
        $maxTot = 0;//maximum student # any more than this and some students might not match
        foreach ($PL as $p) {
            $minTot += $p->min;//one no matter what? if so VERY redundant
            $maxTot += $p->max;//varies
        }

        $minCheck = false;//check if enough students 
        $maxCheck = false;//check if too many students!
        $maxDif = 0;
        $minDif = 0;
        $totStudents = count($SL); //total # of active students
        if (!($minTot <= $totStudents)) {
            $minDif = $minTot - $totStudents;//too few students find out how many more needed
        } else {//if  enough students marked as checked yup enough students good
            $minCheck = true;
        }
        if (!($maxTot >= $totStudents)) {
            $maxDif = $totStudents - $maxTot;//too many students find out difference
        } else {//if not too many students mark as checked, yup enough students 
            $maxCheck = true;
        }

        //check if enough students and not too many students, just right!
        if (($minCheck && $maxCheck) != true) {
            $minMsg = '';
            $maxMsg = '';
            if ($minCheck == false) {//errors errors everywhere go do em i guess
                if ($minDif == 1) {
                    $minMsg = ('Sorry, cannot continue. Please unrank projects. Need 1 more student to satisfy project minimum. <br />');
                } else if ($minDif > 2) {
                    $minMsg = ('Sorry, cannot continue. Please unrank projects. Need ' . $minDif . ' more students to satisfy projects\' minimum. <br />');
                }
            }
            if ($maxCheck == false) {
                if ($maxDif == 1) {
                    $maxMsg = ('Sorry, cannot continue. Please rank or add more projects. Need to find projects for 1 more student. <br />');
                } else if ($maxDif > 2) {
                    $maxMsg = ('Sorry, cannot continue. Please rank or add more projects. Need to find projects for ' . $maxDif . ' more students. <br />');
                }
            }
            if ($maxCheck == false && $minCheck == false) {
                $msg = $minMsg . "<br />" . $maxMsg;//is this possible?
            } else if ($maxCheck == false && $minCheck == true) {
                $msg = $maxMsg;
            } else if ($maxCheck == true && $minCheck == false) {
                $msg = $minMsg;
            }
            setErrorFlashMessage($this, $msg);
            redirect('match');
        }


        // Phase 2 Preprocessing
        // no students have these skills
        $leftOverSkills = array();
        // students have these skills the remain variable shows 
        // how many more students needed to have these skills 
        // to fulfill all projects with these skills missing
        $neededSkills = array();
        foreach ($pskillCount as $skill => $count) {
            if (array_key_exists($skill, $sskillCount)) {
                if (!($count <= $sskillCount[$skill])) {
                    $remain = $count - $sskillCount[$skill];
                    $neededSkills[$skill] = $remain;
                }
            } else {
                array_push($leftOverSkills, $skill);
            }
        }

        $leftOverCheck = false;
        $neededCheck = false;
        if (!empty($leftOverSkills)) {
            sort($leftOverSkills, SORT_STRING);//uh oh matching won't happen!
        } else {
            $leftOverCheck = true;//all skills for all projects accounted for yay!
        }
        if (!empty($neededSkills)) {
            ksort($neededSkills, SORT_STRING);
            $needed = array();
            foreach ($neededSkills as $skills => $num) {
                array_push($needed, $skills);
            }
        } else {
            $neededCheck = true;//enough students know all skills i for all projects j 
        }

        if ($neededCheck && $leftOverCheck) {//are there no missing skills/ defecient skills
            foreach ($PL as $p) {
                foreach ($SL as $s) {
                    if ($p->checkStudent($s)) {
                        $p->addDesiredStudent($s);
                    }
                }
            }
            $_SESSION['PL'] = $PL;
            $_SESSION['SL'] = $SL; 
            $this->doMatch();
        } else {//bad
            $data['neededCheck'] = $neededCheck;
            $data['leftOverCheck'] = $leftOverCheck;
            if (isset($leftOverCheck))
                $data['leftOverSkills'] = $leftOverSkills;
            if (isset($needed))
                $data['neededSkills'] = $needed;
            $_SESSION['PL'] = $PL;
            $_SESSION['SL'] = $SL; 
            $this->load->view('match_results_page', $data);
        }
    }

    public function doMatch() {

//        set_time_limit(0);
//        error_reporting(E_ALL);
//        ob_implicit_flush(TRUE);
//        ob_end_flush();
        // LOAD PRE-PREPARED STUDENTS from preprocess i guess?
          $PL = $_SESSION['PL'];
          $SL = $_SESSION['SL'];

//        $p1 = new Project('1', 'Proj 1', array('1' => 'sk1', '2' => 'sk2', '3' => 'sk3'), '1', '3');
//        $p2 = new Project('2', 'Proj 2', array('1' => 'sk1', '2' => 'sk2'), '3', '2');
//        $p3 = new Project('3', 'Proj 3', array('2' => 'sk2', '4' => 'sk4'), '2', '3');
//
//        $PL = array($p1, $p2, $p3);

        $pskillSet = array();//array of array of skills
        foreach ($PL as $p) {
            foreach ($p->skills as $s) {
                array_push($pskillSet, $s);
            }
        }
        
        usort($PL, 'compare_ranks');//sort project list via ranking

        $numProjects = count($PL);//how many projects
        $previousRank = null;//rank of project before p 
        foreach ($PL as $p) {//truly rank all projects relative to one another
            if (intval($p->score) == $previousRank) {//why intval
                $numProjects++;
                $previousRank = intval($p->score);
                $p->score = $numProjects;
            } else {
                $previousRank = intval($p->score);
                $p->score = $numProjects;
            }
            if ($numProjects > 1) {
                $numProjects--;
            }
        }

        //print_r($pskillSet);
        $pskillCount = array_count_values_ignore_case($pskillSet);//array[skill name] = amount of skills in it

//        $rL1 = array('1' => '2', '2' => '1', '3' => '1');
//        $rL2 = array('1' => '3', '2' => '6', '3' => '6');
//        $rL3 = array('1' => '1', '2' => '4', '3' => '7');
//        $rL4 = array('1' => '1', '3' => '1');
//        $rL5 = array('1' => '2', '2' => '1');
//
//        $s1 = new Student('1', 'Chris', array('1' => 'sk1', '2' => 'sk2', '3' => 'sk3'), $rL1);
//        $s2 = new Student('2', 'Alicia', array('1' => 'sk1', '2' => 'sk2', '4' => 'sk4'), $rL2);
//        $s3 = new Student('3', 'Michael', array('1' => 'sk1', '3' => 'sk3'), $rL3);
//        $s4 = new Student('4', 'Steven', array('2' => 'sk2', '4' => 'sk4'), $rL4);
//        $s5 = new Student('5', 'Lorenzo', array('3' => 'sk3', '4' => 'sk4'), $rL5);
//
//        $SL = array($s1, $s2, $s3, $s4, $s5);

        foreach ($SL as $s) {//truly rank student rankings
            $minimum = 2;
            $previousRank = null;
            $x = 0;
            foreach ($s->scoreList as $project => $rank) {
                if (intval($rank) == $previousRank) {
                    if ($minimum > 1) {
                        $minimum++;
                    }
                    $s->scoreList[$project] = $minimum;
                } else {
                    $s->scoreList[$project] = $minimum;
                }
                if ($minimum > 1) {
                    $minimum--;
                }
                $previousRank = intval($rank);
                $x++;
            }
            // echo '<br>';
        }

        $sskillSet = array();
        foreach ($SL as $s) {//make list of all student skills plus amount
            foreach ($s->skills as $sk) {
                array_push($sskillSet, $sk);
            }
        }

        // print_r($sskillSet);
        $sskillCount = array_count_values_ignore_case($sskillSet);//array of skills #

        foreach ($PL as $p) {
            foreach ($SL as $s) {
                if ($p->checkStudent($s)) {//if students has at least one skill for p
                    $p->addDesiredStudent($s);//add student
                }
            }
        }

        $LTL = array();//list of team list?
        
        //What happens: for each project!, get teamlist!, made all possible arrangements of students for project,
        //for each arranement, made a team out of that, addedum it to TL
        //then push TL for LTL for projects
        //End result LTL is  = array where ith element is array for a project where jth element is possibteam for that project 
        foreach ($PL as $p) {
            $TL = array();//team list
            $arrangement = subsets_up_to_n($p->desiredStudents, $p->max);// get all arrangements?
            foreach ($arrangement as $a) {
                $team = new Team($p->id, $p->name, $p->score, $p->skills);
                $team->addStudents($a);//add arrangement for s
                array_push($TL, $team);
            }
            array_push($LTL, $TL);
            unset($TL);
        }

        $comb = combinations($LTL, count($SL));//?
        $data['doMatch'] = true;
        $data['comb'] = $comb;
        $this->load->view('match_results_page', $data);
    }

}
