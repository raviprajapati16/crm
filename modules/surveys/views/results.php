<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .choice-answer-header {
        display: flex;
        justify-content: space-between;
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-6 animated fadeIn">
                <div class="panel_s">
                    <div class="panel-heading">
                        <?php echo $title; ?>
                    </div>
                    <div class="panel-body">
                        <?php $text_questions = array(); ?>
                        <?php if (count($survey->questions) > 0) {
                            foreach ($survey->questions as $question) {  ?>
                                <div class="mbot20">
                                    <?php if ($question['boxtype'] == 'checkbox' || $question['boxtype'] == 'radio') { ?>
                                        <div class="choice-answer-header">
                                            <h4 class="bold no-mbot"><?php echo $question['question']; ?></h4>
                                            <h5 class="bold no-mbot"> <a href="#aquestionid_<?php echo $question['questionid']; ?>" data-toggle="modal"><?php echo _l('survey_view_all_answers'); ?></a></h5>
                                        </div>
                                        <?php $x = 0;
                                        foreach ($question['box_descriptions'] as $box_description) { ?>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-8">
                                                            <span class="bold"><?php echo $box_description['description']; ?></span>
                                                        </div>
                                                        <div class="col-md-4 text-right">
                                                            <?php
                                                            $total_box_description_answers = total_rows(db_prefix() . 'form_results', array('rel_id' => $survey->surveyid, 'boxdescriptionid' => $box_description['questionboxdescriptionid'], 'rel_type' => 'survey'));
                                                            $total_box_answers = count(surveys_participate_users_details($survey->surveyid));
                                                            ?>
                                                            <?php echo $total_box_description_answers; ?> / <?php echo $total_box_answers; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <?php
                                                    $percent = ($total_box_description_answers > 0 ? number_format(($total_box_description_answers * 100) / $total_box_answers, 2) : 0);
                                                    ?>
                                                    <div class="progress">
                                                        <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $percent; ?>">
                                                            <?php echo $percent; ?>%
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } ?> <!-- End question boxes -->
                                    <?php } else if ($question['boxtype'] == 'input' || $question['boxtype'] == 'textarea') {
                                        $text_questions[] = $question;
                                    } ?> <!-- end if is boxtype || radio -->
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
            </div>



            <?php if (count($survey->questions) > 0) {
                foreach ($survey->questions as $question) {  ?>
                    <?php if ($question['boxtype'] == 'checkbox' || $question['boxtype'] == 'radio') { ?>
                        <?php
                        $results_sets = surveys_participate_users_details($question['rel_id']);
                        ?>
                        <div class="modal fade" id="aquestionid_<?php echo $question['questionid']; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <h4 class="modal-title" id="myModalLabel"><?php echo $question['question']; ?></h4>
                                    </div>
                                    <div class="modal-body">
                                        <ul class="list-unstyled">
                                            <?php
                                            $i = 1;
                                            foreach ($results_sets as $result) {
                                                $answer = "";
                                                $participate_details = surveys_single_participate_details($result['resultsetid']);
                                                $answersIds = surveys_get_choice_question_answers_id($question['questionid'], $question['rel_id'], $result['resultsetid']);
                                                if (!empty($answersIds)) {
                                                    $answersIds = array_column($answersIds, 'boxdescriptionid');
                                                    if (!empty($answersIds)) {
                                                        $answersArr = surveys_get_choice_question_answers($answersIds);
                                                        if (!empty($answersArr)) {
                                                            $answersArr = array_values(array_filter(array_column($answersArr, 'description')));
                                                            $answer = implode(", ", $answersArr);
                                                        }
                                                    }
                                                }
                                            ?>
                                                <li class=" mbot10">
                                                    <?php
                                                    $userTxt = "";
                                                    if (!empty($participate_details)) {
                                                        if (!empty($participate_details['email'])) {
                                                            $userTxt .= $participate_details['email'];
                                                            if (!empty($participate_details['name'])) {
                                                                $userTxt .= " (" . $participate_details['name'] . ")";
                                                            }
                                                            if (!empty($participate_details['lead_id'])) {
                                                                $userTxt .= " <span class='label label-primary'>Lead</span>";
                                                            } else if (!empty($participate_details['contact_id'])) {
                                                                $userTxt .= " <span class='label label-primary'>Customer</span>";
                                                            } else if (!empty($participate_details['staff_id'])) {
                                                                $userTxt .= " <span class='label label-primary'>Staff Member</span>";
                                                            } else if (!empty($participate_details['emailid'])) {
                                                                $userTxt .= " <span class='label label-primary'>From Mail List</span>";
                                                            }
                                                        }
                                                    }
                                                    ?>

                                                    <?php echo $i, ') ' . $userTxt; ?>
                                                    </br>
                                                    <b>Answer :</b> <?php echo $answer; ?>
                                                </li>
                                                <hr>
                                            <?php
                                                $i++;
                                            } ?>
                                        </ul>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr />

                    <?php } ?>
            <?php }
            } ?>
            <?php if (count($text_questions) > 0) { ?>
                <div class="col-md-6">
                    <div class="panel_s">
                        <div class="panel-heading">
                            <?php echo _l('survey_text_questions_results'); ?>
                        </div>
                        <div class="panel-body">
                            <?php
                            $original_questions = $text_questions;

                            foreach ($text_questions as $question) { ?>
                                <h4 class="bold no-mbot"><?php echo $question['question']; ?></h4>
                                <a href="#questionid_<?php echo $question['questionid']; ?>" data-toggle="modal"><?php echo _l('survey_view_all_answers'); ?></a>
                                <?php
                                $answers = surveys_get_text_question_answers($question['questionid'], $question['rel_id']);
                                ?>
                                <div class="modal fade" id="questionid_<?php echo $question['questionid']; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                <h4 class="modal-title" id="myModalLabel"><?php echo $question['question']; ?></h4>
                                            </div>
                                            <div class="modal-body">
                                                <ul class="list-unstyled">
                                                    <?php
                                                    $i = 1;
                                                    foreach ($answers as $answer) {
                                                        $participate_details = surveys_single_participate_details($answer['resultsetid']);
                                                    ?>
                                                        <li class=" mbot10">
                                                            <?php
                                                            $userTxt = "";
                                                            if (!empty($participate_details)) {
                                                                if (!empty($participate_details['email'])) {
                                                                    $userTxt .= $participate_details['email'];
                                                                    if (!empty($participate_details['name'])) {
                                                                        $userTxt .= " (" . $participate_details['name'] . ")";
                                                                    }
                                                                    if (!empty($participate_details['lead_id'])) {
                                                                        $userTxt .= " <span class='label label-primary'>Lead</span>";
                                                                    } else if (!empty($participate_details['contact_id'])) {
                                                                        $userTxt .= " <span class='label label-primary'>Customer</span>";
                                                                    } else if (!empty($participate_details['staff_id'])) {
                                                                        $userTxt .= " <span class='label label-primary'>Staff Member</span>";
                                                                    } else if (!empty($participate_details['emailid'])) {
                                                                        $userTxt .= " <span class='label label-primary'>From Mail List</span>";
                                                                    }
                                                                }
                                                            }
                                                            ?>

                                                            <?php echo $i, ') ' . $userTxt; ?>
                                                            </br>
                                                            <b>Answer :</b> <?php echo $answer['answer']; ?>
                                                        </li>
                                                        <hr>
                                                    <?php
                                                        $i++;
                                                    } ?>
                                                </ul>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr />
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>

</html>