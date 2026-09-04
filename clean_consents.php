<?php
$consents = App\Models\Consent::all();
foreach ($consents as $consent) {
    if ($consent->body) {
        $body = $consent->body;
        // Clean up {"{...}"} to {{...}}
        $body = preg_replace('/\{"\{([^}]+)\}"\}/', '{{$1}}', $body);
        
        // Clean up {"Value"} to Value (if they were already replaced but left with brackets)
        $body = preg_replace('/\{"([^"{}]*)"\}/', '$1', $body);

        // Clean up `{Value }}` artifacts
        $body = preg_replace('/\{([^}]+)\s*\}\}/', '$1', $body);
        
        $consent->body = $body;
        $consent->save();
    }
}

$templates = App\Models\ConsentTemplate::all();
foreach ($templates as $template) {
    if ($template->body) {
        $body = $template->body;
        // Clean up {"{...}"} to {{...}}
        $body = preg_replace('/\{"\{([^}]+)\}"\}/', '{{$1}}', $body);
        $template->body = $body;
        $template->save();
    }
}
echo "Cleaned up database.\n";
