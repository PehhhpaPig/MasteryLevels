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
    WHERE l.levelID > 28 AND l.levelID <= 32
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
    <title>Python Level 8 Test</title>
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
    <h1 class="page-title">Python Level 8 Test</h1>

    <form action="submit_exam.php" method="post" onsubmit="return runSkulptCode();"class="exam-form">
    <input type="hidden" name="subject" value="Python Level 8">
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
            <p id = "code_question" class="instructions">For the final level, Develop a general function to take the radius of a circle as input, and return its area. The math module is already imported for you.</p>
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
document.getElementById("codeEditor").innerHTML = "import math";
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
 
function runSkulptCode() {
    var prog = document.getElementById("codeEditor").value;
    var resultField = document.getElementById("code_correct");
    
    // Generate random radius (1-10)
    var randInt = Math.floor(Math.random() * 10) + 1;
    console.log("randInt: " + randInt);
    
    // Check if user defined a function
    const funcMatch = prog.match(/^\s*def\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/m);
    
    if (!funcMatch) {
        alert("❌ You must define a function to calculate the area of a circle");
        resultField.value = 0;
        return true;
    }
    
    // Check for return statement
    if (!prog.includes("return")) {
        alert("❌ Your function must use 'return' to return the calculated area");
        resultField.value = 0;
        return true;
    }
    
    // Add print statement to call the function
    prog += "\nprint(" + funcMatch[1] + "(" + randInt + "))";
    console.log(prog);
    
    // Variable to capture output
    var mypre = "";
    
    function outf(text) {
        mypre = text.trim();
        console.log("Output: " + text);
    }
    
    // Configure and run Skulpt
    Sk.configure({output: outf, read: builtinRead});
    
    var myPromise = Sk.misceval.asyncToPromise(function() {
        return Sk.importMainWithBody("<stdin>", false, prog, true);
    });
    
    myPromise.then(
        function(mod) {
            console.log('success');
            
            // Calculate expected area
            var expectedArea = Math.PI * (randInt * randInt);
            var userResult = parseFloat(mypre);
            
            console.log("Expected: " + expectedArea);
            console.log("User result: " + userResult);
            console.log("Difference: " + Math.abs(expectedArea - userResult));
            
            // Check if result is correct
            if (Math.abs(expectedArea - userResult) < 0.01) {
                alert("✅ Correct! Area of circle with radius " + randInt + " = " + userResult.toFixed(4));
                resultField.value = 1;
                return true;
            } else {
                alert("❌ Incorrect. For radius " + randInt + ", expected " + expectedArea.toFixed(4) + " but got " + userResult);
                resultField.value = 0;
                return true;
            }
        },
        function(err) {
            console.log(err.toString());
            alert("❌ Error in your code: " + err.toString());
            resultField.value = 0;
        }
    );
    
    return true;
}

function builtinRead(x) {
    if (Sk.builtinFiles === undefined || Sk.builtinFiles["files"][x] === undefined)
        throw "File not found: '" + x + "'";
    return Sk.builtinFiles["files"][x];
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
