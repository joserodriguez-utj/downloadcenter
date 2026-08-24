<?php
define('CLI_SCRIPT', true);
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/testing/generator/lib.php');

global $DB;

// Simular sesión de admin para poder crear archivos/draft areas.
$admin = get_admin();
\core\session\manager::set_user($admin);

$courseid = 17;
$saulid = 1108;

// Verificar que el alumno exista.
$saul = $DB->get_record('user', ['id' => $saulid], '*', MUST_EXIST);

// Verificar que esté enrolado en el curso.
$context = context_course::instance($courseid);
if (!is_enrolled($context, $saulid)) {
    $studentrole = $DB->get_record('role', ['shortname' => 'student']);
    enrol_try_internal_enrol($courseid, $saulid, $studentrole->id);
}

// Crear la actividad workshop con estrategia acumulativa.
$generator = new \testing_data_generator();
$workshopgen = $generator->get_plugin_generator('mod_workshop');

$workshop = $workshopgen->create_instance([
    'course' => $courseid,
    'name' => 'Taller de prueba: Debate de caso (Saúl)',
    'intro' => 'Actividad de taller creada para probar la descarga de evidencias.',
    'section' => 1,
    'strategy' => 'accumulative',
    'grade' => 100,
    'gradinggrade' => 0,
    'submissionstart' => time() - 3600,
    'submissionend' => time() + 86400,
    'nattachments' => 1,
]);

$cm = get_coursemodule_from_instance('workshop', $workshop->id, $courseid);
$workshopcontext = context_module::instance($cm->id);

// Crear una entrega de Saúl.
$submissionid = $workshopgen->create_submission($workshop->id, $saulid, [
    'title' => 'Entrega de Saúl - Debate de caso',
    'content' => "Este es el contenido de la entrega de prueba de Saúl.\n\nArgumentos del debate de caso.",
]);

echo "OK!\n";
echo "Workshop ID: {$workshop->id}\n";
echo "CM ID: {$cm->id}\n";
echo "Submission ID: {$submissionid}\n";
echo "Alumno: {$saul->firstname} {$saul->lastname} (id {$saul->id})\n";
echo "Curso: {$courseid}\n";
