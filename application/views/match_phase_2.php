<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        
        <style>
            td{
                border: 1px solid black;
                vertical-align: top;
            }
            #on-hover{
                display: none;
            }
            #overflow:hover #on-hover{
                display: block;
            }
            table{
                table-layout: fixed;
            }
        </style>
        
        <?php $this->load->view("matchmaking_header");?>
     <!--Note: "warning" to make red; "success" for green-->
     <h1>Other Projects</h1>
     <h6>Choose one of two versions to proceed for match finalization.</h6>
     Note: When applicable green means the skill is fulfilled. Orange unfulfilled. Gray unnecessary (hover student to reveal).
     <table style="width: 1000px">
    <tr>
        <td> <h2>Friendly Matching :</h2></td>
        <td> <h2>Compromise Matching :</h2></td>
    </tr>
        <?php
        $PLc = array_values($PLc);
        $PLf = array_values($PLf);
        
        for($i = 0; $i<count($PLf); $i++){
            echo '<tr>';
            echo '<td>';
            echo '<h3>';
            echo $PLf[$i]->name;
            echo '</h3>';
            echo '';
            echo '<b>Student Interest Average: ';
            echo $PLf[$i]->calculateAvgInterest();
            echo '</b><br>';
            echo '<b>Skill Total Fulfillment: ';
            echo $PLf[$i]->calculateTotalFulfillment();
            echo '%</b><br>';
            echo '<b>Student Average Fulfillment: ';
            echo $PLf[$i]->calculateAvgFulfillment();
            echo '%</b><br>';
            echo '<b>Skill Fulfillment Data:</b><br>';
            foreach ($PLf[$i]->fulfilledSkills as $s) {
                echo '<li class="label label-success skill">';
                echo $s;
                echo '</li>';
                echo ' ';
            }    
            foreach ($PLf[$i]->missingSkills as $s) {
                echo '<li class="label label-warning skill">';
                echo $s;
                echo '</li>';
                echo ' ';
            }

            echo '<br><h5>Students Added:(';
            echo count($PLf[$i]->desiredStudents);
            echo ' out of ';
            echo $PLf[$i]->max;
            echo ')</h5>';
            foreach($PLf[$i]->desiredStudents as $s){
                echo '<h6>';
                echo $s->name;
                echo '</h6>';
                echo 'Interest: ';
                echo $s->scoreList[$PLf[$i]->id];
                echo '<br>';
                echo '% of Project Skills Acheived: ';
                echo $PLf[$i]->figureSkillContribution($s);
                echo '%<br>';
                echo 'Amount of overflow skills: ';
                echo count($s->overflowSkills);
                echo '<br>';
                echo 'Skill contribution:';
                echo '<br>';

                if(count($s->fufilledSkills)>0){
                    foreach ($s->fufilledSkills as $skill) {
                       echo '<li class="label label-success skill">';
                       echo $skill;
                       echo '</li>';
                       echo ' ';
                   }
                   foreach ($s->missingSkills as $skill) {
                       echo '<li class="label label-warning skill">';
                       echo $skill;
                       echo '</li>';
                       echo ' ';
                    }
               }
               else{
                   echo '<i>No Contribution</i><br>';
               }
               if(count($s->overflowSkills)>0){
                echo '<div id="overflow">';
                echo '<li class="label skill">Hover to reveal overflow skill</li>';
                echo '<div id="on-hover">';  
               }

               foreach ($s->overflowSkills as $skill) {
                   echo '<li class="label skill">';
                   echo $skill;
                   echo '</li>';
                   echo ' ';
               } 
               if(count($s->overflowSkills)>0){
               echo '</div>';
               echo '</div>';
               }

               echo '<br>';
            }
            echo '</td>';
            
            echo '<td>';
                        echo '<h3>';
            echo $PLc[$i]->name;
            echo '</h3>';
            echo '';
            echo '<b>Student Interest Average: ';
            echo $PLc[$i]->calculateAvgInterest();
            echo '</b><br>';
            echo '<b>Skill Total Fulfillment: ';
            echo $PLc[$i]->calculateTotalFulfillment();
            echo '%</b><br>';
            echo '<b>Student Average Fulfillment: ';
            echo $PLc[$i]->calculateAvgFulfillment();
            echo '%</b><br>';
            echo '<b>Skill Fulfillment Data:</b><br>';
            foreach ($PLc[$i]->fulfilledSkills as $s) {
                echo '<li class="label label-success skill">';
                echo $s;
                echo '</li>';
                echo ' ';
            }    
            foreach ($PLc[$i]->missingSkills as $s) {
                echo '<li class="label label-warning skill">';
                echo $s;
                echo '</li>';
                echo ' ';
            }

            echo '<br><h5>Students Added:(';
            echo count($PLc[$i]->desiredStudents);
            echo ' out of ';
            echo $PLc[$i]->max;
            echo ')</h5>';
            foreach($PLc[$i]->desiredStudents as $s){
                echo '<h6>';
                echo $s->name;
                echo '</h6>';
                echo 'Interest: ';
                echo $s->scoreList[$PLc[$i]->id];
                echo '<br>';
                echo '% of Project Skills Acheived:';
                echo $PLc[$i]->figureSkillContribution($s);
                echo '%<br>';
                echo 'Amount of overflow skills: ';
                echo count($s->overflowSkills);
                echo '<br>';
                echo 'Skill contribution:';
                echo '<br>';

                if(count($s->fufilledSkills)>0){
                    foreach ($s->fufilledSkills as $skill) {
                       echo '<li class="label label-success skill">';
                       echo $skill;
                       echo '</li>';
                       echo ' ';
                   }
                   foreach ($s->missingSkills as $skill) {
                       echo '<li class="label label-warning skill">';
                       echo $skill;
                       echo '</li>';
                       echo ' ';
                    }
               }
               else{
                   echo '<i>No Contribution</i><br>';
               }
               if(count($s->overflowSkills)>0){
                echo '<div id="overflow">';
                echo '<li class="label skill">Hover to reveal overflow skill</li>';
                echo '<div id="on-hover">';  
               }
               foreach ($s->overflowSkills as $skill) {
                   echo '<li class="label skill">';
                   echo $skill;
                   echo '</li>';
                   echo ' ';
               }
               if(count($s->overflowSkills)>0){
               echo '</div>';
               echo '</div>';
               }

               echo '<br>';
            }
            echo '</td>';
            echo '</tr>';
        }
        ?>
    <?php
    $unmatchedF = $_SESSION["unmatched"];
    $unmatchedC = $_SESSION["1unmatched"];
    ?>
    <tr>
        <td>
            <h3>Unmatched Students (Friendly)</h3>
            <?php
            if(count($unmatchedF) == 0){
                echo 'All students matched!';
            }
            else{
                foreach($unmatchedF as $s){
                    echo $s->name;
                    echo "<br>";
                }
            }
            
            ?>
        </td>
        <td>
            <h3>Unmatched Students (Compromise)</h3>
            <?php
            if(count($unmatchedC) == 0){
                echo 'All students matched!';
            }
            else{
                foreach($unmatchedC as $s){
                    echo $s->name;
                    echo "<br>";
                }
            }
            
            ?>
        </td>
    </tr>
     
     </table>
    <?php $this->load->view("template_footer"); ?>
    </body>
</html>
