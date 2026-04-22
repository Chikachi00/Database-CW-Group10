<div id="rubricModal" class="moodle-modal-overlay">
    <div class="moodle-modal-box">
        <div class="moodle-modal-header">
            <h2>Assessor Grading Rubric & Help</h2>
            <span class="moodle-close-x" id="closeRubricModalX">&times;</span>
        </div>
        <div class="moodle-modal-body">
            <div style="background-color: #e8f0fe; border-left: 4px solid #10263b; padding: 15px; margin-bottom: 25px; border-radius: 4px;">
                <strong style="color: #10263b; font-size: 16px;"><i class="fas fa-info-circle"></i> Need Assistance?</strong><br>
                <span style="color: #555; font-size: 14px;">If you made an error in grading, require score adjustments after submission, or encounter any system issues, please contact our Database Administrator:</span>
                <div style="margin-top: 8px;">
                    <i class="fas fa-chalkboard-teacher"></i> <a href="https://www.nottingham.edu.my/computer-mathematical-sciences/People/chyecheah.tan" target="_blank" class="admin-link">TAN CHYE CHEAH</a><br>
                    &#9993; <a href="mailto:ChyeCheah.Tan@nottingham.edu.my" class="admin-link">ChyeCheah.Tan@nottingham.edu.my</a>
                </div>
            </div>

            <p><strong><i class="fas fa-list-ol"></i> Assessment Guidelines:</strong></p>
            <p style="color: #555; margin-bottom: 15px; font-size: 14px;">Assessors must evaluate students using the predefined criteria. Please enter the <strong>Raw Marks (0-100)</strong> for each component. The system will automatically calculate the final score based on these strict weightages:</p>
            
            <div style="background-color: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 4px; margin-bottom: 20px;">
                <ul style="column-count: 2; column-gap: 20px; margin: 0; padding-left: 20px; color: #333; font-size: 14px; line-height: 1.6;">
                    <li>Tasks/Projects: <strong>10%</strong></li>
                    <li>Health & Safety: <strong>10%</strong></li>
                    <li>Connectivity/Theory: <strong>10%</strong></li>
                    <li>Report Presentation: <strong>15%</strong></li>
                    <li>Clarity of Language: <strong>10%</strong></li>
                    <li>Lifelong Learning: <strong>15%</strong></li>
                    <li>Project Management: <strong>15%</strong></li>
                    <li>Time Management: <strong>15%</strong></li>
                </ul>
            </div>

            <p style="color: #a94442; background-color: #f2dede; padding: 12px; border-radius: 4px; font-size: 13px; border: 1px solid #ebccd1; margin: 0;">
                <strong><i class="fas fa-exclamation-triangle"></i> Mandatory Requirement:</strong> You must provide qualitative comments to justify the scores given. Weightages are fixed and cannot be modified.
            </p>
        </div>
        <div class="moodle-modal-footer">
            <button id="closeRubricModalBtn" class="moodle-btn-submit" style="margin-top:0; padding: 8px 20px;">Close</button>
        </div>
    </div>
</div>

<script>
    // 独立封装的事件监听，防止干扰主文件逻辑
    document.addEventListener("DOMContentLoaded", function() {
        var rubricModal = document.getElementById("rubricModal");
        var openRubricBtn = document.getElementById("openRubricModalBtn");
        var closeRubricX = document.getElementById("closeRubricModalX");
        var closeRubricBtn = document.getElementById("closeRubricModalBtn");
        
        if(openRubricBtn) {
            openRubricBtn.addEventListener("click", function() {
                rubricModal.style.display = "flex";
            });
        }
        if(closeRubricX) {
            closeRubricX.addEventListener("click", function() {
                rubricModal.style.display = "none";
            });
        }
        if(closeRubricBtn) {
            closeRubricBtn.addEventListener("click", function() {
                rubricModal.style.display = "none";
            });
        }

        window.addEventListener("click", function(event) { 
            if (event.target == rubricModal) {
                rubricModal.style.display = "none";
            }
        });
    });
</script>