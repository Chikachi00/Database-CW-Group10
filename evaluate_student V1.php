<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internship Assessment Entry</title>
    <style>
        /* basic styling */
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        h2 { color: #333; text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; color: #555; }
        input[type="number"], textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .total-box { background: #e9ecef; padding: 15px; text-align: right; font-size: 18px; font-weight: bold; margin-top: 20px; border-radius: 4px; }
        button { width: 100%; padding: 12px; background: #007bff; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; margin-top: 15px; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>

<div class="container">
    <h2>Enter Internship Marks</h2>
    <p style="color: #666; text-align: center;">Evaluating Student: <strong>Alice Wong (S2024001)</strong></p>

    <form action="submit_marks.php" method="POST" id="assessmentForm">
        <input type="hidden" name="internship_id" value="1">

        <div class="form-group">
            <label for="task_score">Undertaking Tasks/Projects (Max 10%):</label>
            <input type="number" id="task_score" name="task_score" min="0" max="10" step="0.1" class="score-input" required>
        </div>

        <div class="form-group">
            <label for="health_safety_score">Health & Safety Requirements (Max 10%):</label>
            <input type="number" id="health_safety_score" name="health_safety_score" min="0" max="10" step="0.1" class="score-input" required>
        </div>

        <div class="form-group">
            <label for="time_mgmt_score">Time Management (Max 15%):</label>
            <input type="number" id="time_mgmt_score" name="time_mgmt_score" min="0" max="15" step="0.1" class="score-input" required>
        </div>

        <div class="form-group">
            <label for="comments">Qualitative Comments:</label>
            <textarea id="comments" name="qualitative_comments" rows="4" placeholder="Enter comments to justify the scores..." required></textarea>
        </div>

        <div class="total-box">
            Live Total Score: <span id="display_total">0.00</span> / 100
        </div>

        <button type="submit">Submit Assessment</button>
    </form>
</div>

<script>
    // JavaScript: 实时计算并验证表单
    const inputs = document.querySelectorAll('.score-input');
    const displayTotal = document.getElementById('display_total');

    inputs.forEach(input => {
        input.addEventListener('input', () => {
            let total = 0;
            inputs.forEach(inp => {
                total += parseFloat(inp.value) || 0; // 如果为空则按 0 算
            });
            displayTotal.innerText = total.toFixed(2);
        });
    });
</script>

</body>
</html>