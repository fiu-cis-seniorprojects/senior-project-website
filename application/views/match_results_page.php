<?php $this->load->view("template_header");?>
<?php if (isset($leftOverCheck) && isset($neededCheck)) { ?>
    <ul>
        <h2>Match Results</h2>
        <h6>Woah! Something went wrong in the second stage of preprocessing.</h6>
        <hr><?php if ($leftOverCheck == false) { ?>
            <div class ='well'>
                <ul class="inline">
                    <h4>Students do not have these skills:</h4>
                    <hr>

                    <p> Please check if any students can add the skills below or remove any projects with the skills below: <br /></p>


                    <ul class="inline">
                        <?php
                        foreach ($leftOverSkills as $skills) {
                            ?>
                            <li class="label skill"><?php echo $skills ?></li>
                            <?php
                        }
                        ?>
                    </ul>

                </ul>
            </div>  
        <?php } ?>
        <?php if ($neededCheck == false) { ?>
            <div class ='well'>
                <h4>Some students have these skills, but some projects may not have these skills satisfied:</h4>
                <hr>

                <p> Please check if any students can add the skills below or remove any projects with the skills below: <br /></p>


                <ul class="inline">
                    <?php
                    foreach ($neededSkills as $skills) {
                        ?>
                        <li class="label skill"><?php echo $skills ?></li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
        <?php } ?>
        <div style='padding-bottom: 1px'>
            <ul style='display:block; margin: 0;'>
                <?php
                echo form_open('match/#run match', array(
                ));
                ?>
                <li style = 'float: left; overflow: hidden;'>
                    <?php
                    echo form_submit(array(
                        'id' => 'back',
                        'name' => 'back',
                        'type' => 'Submit',
                        'class' => 'btn btn-primary',
                        'value' => 'Back'
                    ));
                    ?>
                </li> 
                <?php echo form_close() ?>
                <?php
//                echo form_open('match/doMatch', array(
//                ));
//                ?>
                <li style = 'float: right; overflow: hidden;'>
                    <?php
//                    echo form_submit(array(
//                        'id' => 'continue',
//                        'name' => 'continue',
//                        'type' => 'Submit',
//                        'class' => 'btn btn-primary',
//                        'value' => 'Continue'
//                    ));
//                    ?>
                </li>
                <?php //echo form_close() ?>
            </ul> 
        </div>
    <?php
    } else if (isset($doMatch)) {
        $x = 1;
        ?>
        <ul class="project_list unstyled ">
            <?php
            foreach ($comb as $c) {
                echo '<div class="well project_list">';
                echo "<center><h3>Tentative Match " . $x . ':</h3></center><hr>'
                        . '<h4 style="float: left"> Professor Score = ' . $c->profScore . '</h4> <h4 style="float: right">   Student Score = ' . $c->studentScore . '</h4><br><br/>';

                foreach ($c->projects as $p) {
                    echo '<li class="well project_list"><h5>';
                    echo $p->projName;
                    echo "</h5><ul class='unstyled inline'><lh class='muted'>Team Members:</lh>";
                    foreach ($p->members as $m) {
                        echo '<li>';
                        echo $m->name;
                        echo '</li>';
                    }
                    echo '</ul></li>';
                }
                $x++;
                echo '</div>';
                echo '<hr />';
            }
            ?>
            <?php
        }
        ?>
    </ul>
     <!--Note: "warning" to make red; "success" for green-->
        <h1>Match Result Page</h1>
        
        <h3>Successful Matchings</h3>
        <!--Put results here-->
        <hr>
        <h3>Unmatched Projects</h3>
        <h6>Following projects could not be validated for matching. Red skills are skills no active students have. If a project has no red skills
        then no students wanted it.</h6>
        <?php
        foreach ($IPL as $p){
            $missingSkill = array_intersect($p->skills, $p->studentAccumSkills);
            echo $p->name;
            echo '<br>';
            foreach($p->skills as $iskill){
                if(in_array($iskill, $missingSkill)){
                    echo '<li class="label skill label-warning">';
                }
                else{
                    echo '<li class="label skill">';
                }
                echo $iskill;
                           
                echo '</li>';
            }
            echo '<br>';
        }
        ?>
        <h6>Following projects could not be matched via matchmaking.</h6>
        <hr>
        <h3>Unmatched Students</h3>
        <h6>The following students did not rank "rank minimum" number of projects.</h6>
        <?php
        foreach ($ISL as $s){
            echo $s->name;
            echo '<br>';
        }
        ?>
        <h6>Following students could not be matched via matchmaking.</h6>

    <?php $this->load->view("template_footer"); ?>



