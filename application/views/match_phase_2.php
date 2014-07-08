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
        
        <?php $this->load->view("matchmaking_header");
        $MDc = $_SESSION['PLcMD'];
        $MDf = $_SESSION['PLfMD'];
        $unmatchedF = $_SESSION["unmatched"];
        $unmatchedC = $_SESSION["1unmatched"];?>
     <!--Note: "warning" to make red; "success" for green-->
     <h1>Match Phase 2: Other Projects</h1>
     <h6>Choose one of two versions of the national residency matchmaking process (NRMP) to proceed for match finalization.</h6>
     Note: When applicable green means the skill is fulfilled. Orange unfulfilled. Gray unnecessary (hover to reveal).
     <table style="width: 1000px">
    <tr>
        <td> <h2>Friendly NRMP Matching</h2>
            <b>Overall Match Data</b><br>
            Student Average Interest: <?php echo $MDf->avgInterest;?><br>
        Average Total Skill Fulfillment: <?php echo $MDf->avgTotalSkill;?>%<br>
        Student Average Fulfillment <?php echo $MDf->avgAvgFulfillment;?>%<br>
        Total Overflow Skills: <?php echo $MDf->totalOverflow;?><br>
        Amount of Unmatched Students: <?php echo count($unmatchedF);?> </td>
        <td> <h2>Compromise NRMP Matching</h2>
            <b>Overall Match Data</b><br>
        Student Average Interest: <?php echo $MDc->avgInterest;?><br>
        Average Total Skill Fulfillment: <?php echo $MDc->avgTotalSkill;?>%<br>
        Student Average Fulfillment <?php echo $MDc->avgAvgFulfillment;?>%<br>
        Total Overflow Skills: <?php echo $MDc->totalOverflow;?><br>
        Amount of Unmatched Students: <?php echo count($unmatchedC);?></td>
    </tr>
        <?php
        $PLc = array_values($_SESSION['PLc']);
        $PLf = array_values($_SESSION['PLf']);
        
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
     
     </table><br>
     <table style="width: 1000px">
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
            ?></td>
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
     
     <br><b>Choose one of the two match results and proceed. Compromised is default.</b>
     <form>
         <input type="radio" name="OtherProject" value="friendly" >Friendly
         <input type="radio" name="OtherProject" value="compromise" checked="true">Compromise
     </form>
     <?php// $_SESSION['otherProjectState']= $_POST["OtherProject"];?>
    <?php $this->load->view("template_footer"); ?>
    </body>
</html>
