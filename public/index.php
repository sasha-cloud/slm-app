<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require __DIR__ . '/../src/routes.php';

//http://adamstanford.net/slim-framework-and-swift-mailer
//http://swiftmailer.org/docs/sending.html
// Create Transport
$transport = Swift_MailTransport::newInstance();

// Create Mailer with our Transport.
$mailer = Swift_Mailer::newInstance($transport);

// Notice we pass along that $mailer we created in index.php
$app->get('/test-email', function() use ($app, $mailer){
    
    // Here I'm fetching my email template from my template directory.
    $welcomeEmail = $app->view->fetch('emails/welcome.php');
    
    // Setting all needed info and passing in my email template.
    $message = Swift_Message::newInstance('Wonderful Subject')
                    ->setFrom(array('sasha.cloud@outlook.com' => 'Me'))
                    ->setTo(array('sasha.cloud@outlook.com' => 'You'))
                    ->setBody($welcomeEmail)
                    ->setContentType("text/html");

    // Send the message
    $results = $mailer->send($message);
    
    // Print the results, 1 = message sent!
    print($results);

});    

// Run app
$app->run();
