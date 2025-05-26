<?php

session_start();


if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}


// Database credentials
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'exam_website';

// Connect to MySQL
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->select_db($database);


// --- RANDOM MULTIPLE CHOICE SECTION ---

$questionQuery = "
    SELECT q.questionID, q.question 
    FROM Questions q
    INNER JOIN Levels l ON q.levelID = l.levelID
    WHERE l.levelID > 16 AND l.levelID <= 20
    ORDER BY RAND()
    LIMIT 3;
";

$questionResult = $conn->query($questionQuery);

if (!$questionResult) {
    die("Error fetching questions: " . htmlspecialchars($conn->error));
}

$questions = [];

while ($row = $questionResult->fetch_assoc()) {
    $questions[] = $row;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Python Level 6 Test</title>
    <link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js" type="text/javascript"></script> 
  	<script src="https://cdn.jsdelivr.net/gh/Tezumie/Skulpt-CDN@latest/skulpt.min.js"></script>
  	<script src="https://cdn.jsdelivr.net/gh/Tezumie/Skulpt-CDN@latest/skulpt-stdlib.js"></script>
    <script>hljs.highlightAll();</script>
    <style>
        /* Extra Clean Styling */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .main-content {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .page-title {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2em;
            color: #333;
        }
        .exam-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .question-section {
            background: #f1f5f9;
            padding: 20px;
            border-radius: 8px;
        }
        .question-text {
            font-size: 1.2em;
            margin-bottom: 15px;
            color: #222;
        }
        .answer-option {
            margin-bottom: 10px;
        }
        .answer-option input[type="radio"] {
            margin-right: 10px;
        }
        .coding-question-section {
            background: #eef2f7;
            padding: 50px;
            border-radius: 8px;
        }
        .instructions {
            margin-bottom: 10px;
            font-size: 1em;
        }
        .code-textarea {
            width: 100%;
            height: 250px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 1em;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            resize: vertical;
            background: #f9f9f9;
        }
        .submit-section {
            text-align: center;
            margin-top: 20px;
        }
        .submit-button {
            background: #4CAF50;
            color: white;
            padding: 12px 25px;
            font-size: 1em;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .quit-button {
            background:rgb(172, 34, 29);
            color: white;
            padding: 12px 25px;
            font-size: 1em;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .submit-button:hover {
            background: #45a049;
        }
        .question-divider {
            border: none;
            height: 1px;
            background: #ddd;
            margin: 30px 0;
        }
        #codeMirror {
    position: absolute;
    top: 0;
    left: 0;
    padding: 1em;
    width: 100%;
    height: 100%;
    pointer-events: none;
    white-space: pre-wrap;
    word-wrap: break-word;
    z-index: 1;
    overflow-y: auto;
    border-radius: 8px;
    background: #1e1e1e;
}
#language-python {
    pointer-events: none;      /* prevent interaction */
    position: absolute;
    top: 0;
    left: 0;
    z-index: 1;
    padding: 1em;
    border-radius: 6px;


}
#codeEditor {
    position: relative;
    z-index: 2;
    padding: 1em;
    width: 100%;
    min-height: 200px;
    color: transparent;
    background: transparent;
    border: none;
    resize: none;
    overflow: auto;
    caret-color: #ffffff; /* make cursor visible */
    font-family: 'Fira Code', monospace;
    font-size: 1rem;
    line-height: 1.4;
}
#codeInput {
    position: relative;
    background: #1e1e1e;
    color: #000;
    z-index: 2;
    font-size: 1pt;
}
.code-editor-wrapper {
    position: relative;
    width: 96%;
    max-width: 900px;
    min-height: 200px;
    max-height: 250px;
    font-family: 'Fira Code', monospace;
    font-size: 1rem;
    line-height: 1.4;
}
        
    </style>
</head>
<body>

<div class="main-content">
    <h1 class="page-title">Python Level 6 Test</h1>

    <form action="submit_exam.php" method="post" onsubmit="return runSkulptCode();"class="exam-form">
    <input type="hidden" name="subject" value="Python Level 6">
    <div style="text-align: right; margin-bottom: 20px;">
    <a href="python_splash.php" class="quit-button" onclick="return confirm('Are you sure you want to quit and return to the menu?');">Quit</a>
</div>
        <!-- Dynamically Generated Multiple Choice Questions -->
        <?php
        foreach ($questions as $index => $question) {
            echo "<div class='question-section'>";
            echo "<h2 class='question-text'>Question " . ($index + 1) . ": " . htmlspecialchars($question['question']) . "</h2>";

            // Fetch corresponding answers
            $answerQuery = "
                SELECT answerID, answer, answer_character
                FROM Answers
                WHERE questionID = ?
                ORDER BY answer_character ASC
            ";

            $stmt = $conn->prepare($answerQuery);
            if (!$stmt) {
                die("Error preparing answer query: " . htmlspecialchars($conn->error));
            }

            $stmt->bind_param("i", $question['questionID']);
            $stmt->execute();
            $answerResult = $stmt->get_result();

            if ($answerResult->num_rows == 0) {
                echo "<p class='no-answers-warning'>No answers available for this question.</p>";
            } else {
                while ($answer = $answerResult->fetch_assoc()) {
                    echo "<div class='answer-option'>";
                    echo "<label>";
                    echo "<input type='radio' name='question_" . $question['questionID'] . "' value='" . htmlspecialchars($answer['answer_character']) . "' required> ";
                    echo "<span class='answer-text'>" . htmlspecialchars($answer['answer_character']) . ". " . htmlspecialchars($answer['answer']) . "</span>";
                    echo "</label>";
                    echo "</div>";
                }
            }
            echo "</div><hr class='question-divider'>";
        }
        ?>

        <!-- Coding Question Section -->
        <div class="coding-question-section">

<iframe src="https://trinket.io/embed/python/30d1b8ad2c9f" width="100%" height="300" frameborder="0" marginwidth="0" marginheight="0" allowfullscreen></iframe>

            <h2 class="question-text">Python Coding Question:</h2>
            <p id = "code_question" class="instructions">Declare a list and use a for loop to print each element. Example: fruits = ['apple', 'banana', 'orange']</p>
            <div class="code-editor-wrapper">
            <textarea name="code_answer" class="code-textarea" id="codeEditor"
            placeholder="Use the IDE above to test and develop your code..." 
            required spellcheck="false"></textarea>
            <input id="code_correct" hidden name="code_correct"></input>
            <pre><code id="codeMirror" class = "language-python"></code></pre>
            <b></b>
            
            </div>

            <script>
                const input = document.getElementById("codeEditor");
                const mirror = document.getElementById("codeMirror");

input.addEventListener("input", () => {
    // Copy and escape content
    const code = input.value
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");
    
    mirror.innerHTML = code;
    // Re-highlight
    mirror.removeAttribute("data-highlighted");
    hljs.highlightElement(mirror);
});
            </script>
            <script>
                document.getElementById('codeEditor').addEventListener('keydown', function(e) {
                if (e.key === 'Tab') {
                e.preventDefault();
        let start = this.selectionStart;
        let end = this.selectionEnd;

        // Set textarea value to: text before caret + 4 spaces + text after caret
        this.value = this.value.substring(0, start) + "    " + this.value.substring(end);

        // Move caret
        this.selectionStart = this.selectionEnd = start + 4;
        hljs.highlightAll();
    }
});
</script>

<script type="text/javascript"> 
function highlightPreset(){
        const input = document.getElementById("codeEditor");
        const mirror = document.getElementById("codeMirror");
        // Copy and escape content
        const code = input.value
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");
    
        mirror.innerHTML = code;
        // Re-highlight
        mirror.removeAttribute("data-highlighted");
        hljs.highlightElement(mirror);
    }
    highlightPreset();
    function builtinRead(x) {
        if (Sk.builtinFiles === undefined || Sk.builtinFiles["files"][x] === undefined)
        throw `File not found: '${x}'`;
        return Sk.builtinFiles["files"][x];
}

   function runSkulptCode() {
    const userCode = document.getElementById("codeEditor").value;
    let outputLines = [];
    const resultField = document.getElementById("code_correct");

    // Check for required patterns
    const hasListDeclaration = /\w+\s*=\s*\[.*?\]/.test(userCode);
    const hasForLoop = /for\s+\w+\s+in\s+\w+\s*:/.test(userCode);
    const hasPrint = /print\s*\(/.test(userCode);
    
    // Extract list variable name and for loop variable
    const listMatch = userCode.match(/(\w+)\s*=\s*\[.*?\]/);
    const forLoopMatch = userCode.match(/for\s+(\w+)\s+in\s+(\w+)\s*:/);
    
    // Basic syntax validation
    if (!hasListDeclaration) {
        alert("❌ Your code must declare a list using the format: variable_name = [items]");
        resultField.value = 0;
        return true;
    }
    
    if (!hasForLoop) {
        alert("❌ Your code must use a for loop with the format: for element in list:");
        resultField.value = 0;
        return true;
    }
    
    if (!hasPrint) {
        alert("❌ Your code must use print() to display each element");
        resultField.value = 0;
        return true;
    }
    
    // Check if the for loop is iterating over the declared list
    if (listMatch && forLoopMatch) {
        const listVarName = listMatch[1];
        const loopIterator = forLoopMatch[1];
        const loopTarget = forLoopMatch[2];
        
        if (loopTarget !== listVarName) {
            alert(`❌ Your for loop should iterate over the list you declared (${listVarName})`);
            resultField.value = 0;
            return true;
        }
        
        // Check if print uses the loop variable
        const printPattern = new RegExp(`print\\s*\\(\\s*${loopIterator}\\s*\\)`);
        if (!printPattern.test(userCode)) {
            alert(`❌ Your print statement should print the loop variable (${loopIterator})`);
            resultField.value = 0;
            return true;
        }
    }
    
    // Configure Skulpt
    Sk.configure({
        output: function(text) {
            const trimmed = text.trim();
            if (trimmed !== "") {
                outputLines.push(trimmed);
            }
        },
        read: function(x) {
            if (Sk.builtinFiles === undefined || Sk.builtinFiles["files"][x] === undefined)
                throw "File not found: '" + x + "'";
            return Sk.builtinFiles["files"][x];
        },
        execLimit: 5000  // Prevent infinite loops
    });

    // Execute the code
    Sk.misceval.asyncToPromise(() => Sk.importMainWithBody("<stdin>", false, userCode))
        .then(() => {
            // Check if output was produced
            if (outputLines.length === 0) {
                alert("❌ Your code didn't produce any output. Make sure you're printing each element.");
                resultField.value = 0;
                return;
            }
            
            // Extract the list content to verify output
            const listContentMatch = userCode.match(/=\s*\[(.*?)\]/s);
            if (listContentMatch) {
                const listContent = listContentMatch[1];
                // Parse list items (this is simplified - doesn't handle all edge cases)
                const items = listContent.split(',').map(item => {
                    item = item.trim();
                    // Remove quotes if string
                    if ((item.startsWith('"') && item.endsWith('"')) || 
                        (item.startsWith("'") && item.endsWith("'"))) {
                        return item.slice(1, -1);
                    }
                    return item;
                }).filter(item => item.length > 0);
                
                // Verify output matches list length
                if (outputLines.length !== items.length) {
                    alert(`❌ Expected ${items.length} lines of output, but got ${outputLines.length}. Make sure you're printing each element once.`);
                    resultField.value = 0;
                    return;
                }
            }
            
            // Success!
            alert(`✅ Success! Your code correctly:\n- Declares a list\n- Uses a for loop to iterate through it\n- Prints each element\n\nOutput:\n${outputLines.join('\n')}`);
            resultField.value = 1;
        })
        .catch(err => {
            alert("❌ Error in your code:\n" + err.toString());
            resultField.value = 0;
        });

    return true;
}

                  
</script>
        </div>

        <div class="submit-section">
            <button type="submit" class="submit-button">Submit Test</button>

        </div>
    <script>
document.querySelector("form").addEventListener("submit", function () {
    const questionText = document.getElementById("code_question").innerText.trim();
    document.getElementById("hidden_code_question").value = questionText;
});
</script>
    <input type="hidden" name="code_question" id="hidden_code_question" />
    </form>
</div>
            <div id="output" hidden>Output</div>
</body>
</html>
