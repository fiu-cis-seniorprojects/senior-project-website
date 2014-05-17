<div id="progress" style="display: none; text-align: center;">
    <img alt="Press the back button on your browser re-rank and save. Then you can click Run Match again." src="img/loader.gif">
    <br><br>
</div>

<div id='area'>
    <?php $this->load->view("template_header"); ?>
    <h3>Welcome to the Senior Project Website Matching Algorithm page!</h3>
    <p>Want to match students to projects efficiently? Click on the <a href="#runmatch">Run Match</a> button below.</p>
    <p>As a reminder, projects with the rank "0" will not be considered in the matching algorithm!!!</p>
    <hr>
    <?php
    echo form_open('match/saveRank', array(
        'class' => 'form-inline',
    ));
    ?>

    <?php if ($no_results) { ?>

        <p>There are no Current Projects to rank</p>

    <?php } else { ?>
        <?php if (isset($lRegularProjects) && count($lRegularProjects) > 0) { ?>
            <?php
            $this->load->view('subviews/project_match_list', array(
                'lProjects' => $lRegularProjects,
            ))
            ?>
        <?php } ?>
    <?php } ?>
    <div style='padding-bottom: 30px'>
        <ul style='float: right; display:inline-block; margin: 0 0 3px 0;'> 
            <li style='margin: 0 0 3px 0; overflow: hidden'>
                <?php
                echo form_submit(array(
                    'id' => 'save rank',
                    'name' => 'save rank',
                    'type' => 'Submit',
                    'class' => 'pull-right btn btn-primary',
                    'value' => 'Save Priority Ranking Scheme'
                ));
                ?>
            </li>

            <?php echo form_close() ?>

            <?php
            echo form_open('match/preProcessSteps', array(
                'class' => 'form-inline',
            ));
            ?>
            <li style='margin: 0 0 3px 0; overflow: hidden'>
                <?php
                echo form_submit(array(
                    'id' => 'runmatch',
                    'name' => 'run match',
                    'type' => 'Submit',
                    'class' => 'pull-right btn btn-primary',
                    'value' => 'Run Match'
                ));
                ?>
            </li>
        </ul> 
        <?php echo form_close() ?>
        <script>
            $('#runmatch').click(function(event) {
                $('#area').empty(); 
                document.getElementById("progress").style.display = "block";
                $.post("match/preProcessSteps") 
                   
                        .done(function(data) {
                            //alert("Data Loaded: " + data);
                           document.getElementById("progress").style.display = "none";
                            $('#area').html(data);
                        })
                        .fail(function() {
                            alert("error");
                        });
                event.preventDefault();
            });

        </script>

    </div>

    <?php $this->load->view("template_footer"); ?>

</div>



