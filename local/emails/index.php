<?php
require_once('../../config.php');
require_login();

global $USER, $DB, $CFG;

$PAGE->set_url(new moodle_url('/local/emails/index.php'));
$context = context_system::instance();
$PAGE->set_context($context);

// Check permissions
if (!preg_match('/(logistic|manager)/i', $USER->profile['role']) || !has_capability('local/emails:access', $context)) {
    echo $OUTPUT->header();       
    $message = "<h1><strong>Error 403.</strong> You don't have permission to access to this content.</h1> <p>Contact with the admin for more information.</p>";
    echo html_writer::div($message);
    echo html_writer::div('<a class="btn btn-primary" href="'.$CFG->wwwroot.'">Go back</a>');       
    echo $OUTPUT->footer();   
    return;
}

// Create form instance
$mform = new \local_emails\form\emailForm();

// Form processing
if ($formdata = $mform->get_data()) {
    require_once($CFG->libdir . '/filelib.php');
    error_log("🟢 Form submitted with subject: {$formdata->subject}");

    // Guardar archivos del filemanager
    $draftitemid = $formdata->attachments;
    file_save_draft_area_files(
        $draftitemid,
        \context_system::instance()->id,
        'local_emails',
        'attachments',
        $draftitemid,
        ['subdirs' => 0, 'maxfiles' => 5, 'accepted_types' => '*']
    );
    error_log("Array con metadatos {$formdata->attachments}");
    // Create an instance of a helper class or use a local function
    $success = send_emails_to_users($formdata,$draftitemid);
    
    if ($success) {
                
        // Redirect with success message
        redirect(new moodle_url('/local/emails/index.php'), 
                get_string('emailsent', 'local_emails'), 
                null, 
                \core\output\notification::NOTIFY_SUCCESS);
    } else {
        // Show error message
        \core\notification::error(get_string('emailnotsent', 'local_emails'));
    }
} 

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();

/**
 * Send emails to selected users (standalone function)
 */
function send_emails_to_users($formdata, $itemid) {
    global $DB, $USER, $CFG;
    
    // Get selected users
    $userids = $formdata->userlist;
    
    if (empty($userids)) {
        return false;
    }
    
    // Prepare email content
    $subject = '';
    if (is_array($formdata->subject)) {
        // Por si accidentalmente se trató como un editor
        $subject = $formdata->subject['text'] ?? reset($formdata->subject);
    } else {
        $subject = $formdata->subject;
    }

    $message = $formdata->message_editor['text'];
    $format = $formdata->message_editor['format'];
    
    // Prepare attachments
    $attachments = array();
    if (!empty($itemid)) {
        
        $fs = get_file_storage();
        $context = \context_system::instance();
        
        // Obtener archivos del área de archivos
        $files = $fs->get_area_files(
            $context->id, 
            'local_emails', 
            'attachments', 
            $itemid, 
            'itemid, filepath, filename', 
            false
        );
        
        foreach ($files as $file) {
            if ($file->is_directory()) {
                continue;
            }

            $tempfile = tempnam($CFG->tempdir, 'emlatt');
            $file->copy_content_to($tempfile);

            $attachments = $tempfile;  // ← Solo uno
            $attachmentname = $file->get_filename(); // ← Nombre del archivo
            break; // ← Solo el primer archivo
        }

    }
   
    $success = true;
    $recipient_list=[];
    $failed_list = [];

    foreach ($userids as $userid) {
        $user = $DB->get_record('user', ['id' => $userid]);
        
        if (!$user) {
            $failed_list[] = "Unknown user ID: $userid";
            $success=false;
            continue; // Skip invalid users
        }

        $recipient_list[] = fullname($user) . " ({$user->email})";

        $emailcheck = ($formdata->emailcheck == 0) ? false : true;
        $from = $emailcheck ? $USER : \core_user::get_noreply_user();
        
        // Send email to recipient
        $emailresult = email_to_user(
            $user,
            $from,
            $subject,
            html_to_text($message),
            $message,
            $attachments ?? '',
            $attachmentname ?? ''
        );

        if (!$emailresult) {
            $failed_list[] = fullname($user) . " ({$user->email})";
            $success = false;
            continue;
        } 

         // ✅ Trigger event per recipient
        \local_emails\event\email_sent::create([
            'objectid' => $user->id, // Or null if you don't need it
            'relateduserid' => $user->id,
            'userid' => $USER->id,
            'context' => \context_system::instance(),
            'other' => [
                'recipientid' => $user->id,
                'subject' => $subject,
            ],
        ])->trigger();

    }

    if (!empty($recipient_list) || !empty($failed_list)) {
        $copy_subject = $success ? "Copy: $subject" : "⚠ Some emails failed: $subject";

        // Format successful recipients
        $recipient_summary = "<h3 style='background-color:#0f6cbf;color:white;padding:.3em'>Email sent to:</h3><ul>";
        foreach ($recipient_list as $recipient) {
            $recipient_summary .= "<li>" . $recipient . "</li>";
        }
        $recipient_summary .= "</ul>";

        // Format failures if any
        $failure_summary = '';
        if (!empty($failed_list)) {
            $failure_summary = "<h3 style='background-color:#dc3545;color:white;padding:.3em'>Failed to send to:</h3><ul>";
            foreach ($failed_list as $fail) {
                $failure_summary .= "<li>" . $fail . "</li>";
            }
            $failure_summary .= "</ul>";
        }

        // Final message
        $copy_message = "<h3 style='background-color:#0f6cbf;color:white;padding:.3em'>Original message:</h3><div>{$message}</div><hr>"
            . $recipient_summary
            . $failure_summary;

        email_to_user(
            $USER,
            \core_user::get_noreply_user(),
            $copy_subject,
            html_to_text($copy_message),
            $copy_message,
            $attachments ?? '',
            $attachmentname ?? ''
        );
    }


    
    
    // Limpiar archivos temporales
    if (!empty($attachments) && file_exists($attachments)) {
        @unlink($attachments);
    }

    
    return $success;
}

