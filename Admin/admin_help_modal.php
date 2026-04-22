<style>
    #adminUltimateHelpBox {
        max-width: 550px !important;
        width: 90% !important;
    }
</style>

<div id="rubricModal" class="moodle-modal-overlay">
    <div id="adminUltimateHelpBox" class="moodle-modal-box">
        <div class="moodle-modal-header">
            <h2>Admin Help & Grading Rubric</h2>
            <span class="moodle-close-x" id="closeRubricModalX">&times;</span>
        </div>
        <div class="moodle-modal-body">
            <div style="background-color: #e8f0fe; border-left: 4px solid #10263b; padding: 15px; margin-bottom: 25px; border-radius: 4px;">
                <strong style="color: #10263b; font-size: 16px;"><i class="fas fa-info-circle"></i> Need Assistance?</strong><br>
                <span style="color: #555; font-size: 14px;">If you encounter unexpected behavior, system crashes, issues with deleting linked records, or need database maintenance, please contact:</span>
                <div style="margin-top: 8px;">
                    <i class="fas fa-chalkboard-teacher"></i> <a href="https://www.nottingham.edu.my/computer-mathematical-sciences/People/chyecheah.tan" target="_blank" class="admin-link">TAN CHYE CHEAH</a><br>
                    &#9993; <a href="mailto:ChyeCheah.Tan@nottingham.edu.my" class="admin-link">ChyeCheah.Tan@nottingham.edu.my</a>
                </div>
            </div>

            <p><strong><i class="fas fa-clipboard-list"></i> System Guidelines & Potential Issues:</strong></p>
            <ul style="color: #555; margin-bottom: 15px; font-size: 14px;">
                <li><strong>Record Deletion:</strong> You cannot delete a student or assessor if they have existing internship records linked to them.</li>
                <li><strong>Duplicate Entries:</strong> The system prevents adding users or students with IDs/Usernames that already exist.</li>
                <li><strong>Data Integrity:</strong> All modifications are permanently saved to maintain accurate internship records.</li>
            </ul>

            <p><strong><i class="fas fa-list-ol"></i> Reference: Assessment Weightages (Fixed):</strong></p>
            <div style="background-color: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 4px; margin-bottom: 20px;">
                <ul style="column-count: 2; column-gap: 20px; margin: 0; padding-left: 20px; color: #333; font-size: 14px;">
                    <li style="margin-bottom: 8px;">Tasks/Projects: <strong>10%</strong></li>
                    <li style="margin-bottom: 8px;">Health & Safety: <strong>10%</strong></li>
                    <li style="margin-bottom: 8px;">Connectivity/Theory: <strong>10%</strong></li>
                    <li style="margin-bottom: 8px;">Report Presentation: <strong>15%</strong></li>
                    <li style="margin-bottom: 8px;">Clarity of Language: <strong>10%</strong></li>
                    <li style="margin-bottom: 8px;">Lifelong Learning: <strong>15%</strong></li>
                    <li style="margin-bottom: 8px;">Project Management: <strong>15%</strong></li>
                    <li style="margin-bottom: 8px;">Time Management: <strong>15%</strong></li>
                </ul>
            </div>
        </div>
        <div class="moodle-modal-footer">
            <button id="closeRubricModalBtn" class="moodle-btn-submit" style="margin-top:0; padding: 8px 20px;">Close</button>
        </div>
    </div>
</div>

<script>
    // 立即执行脚本：绝不死机，强制绑定点击事件
    (function() {
        var forceBindEvents = function() {
            var modal = document.getElementById("rubricModal");
            var openBtn = document.getElementById("openRubricModalBtn");
            var closeX = document.getElementById("closeRubricModalX");
            var closeBtn = document.getElementById("closeRubricModalBtn");

            // 强制覆盖 onclick 确保生效
            if (openBtn && modal) {
                openBtn.onclick = function(e) {
                    e.preventDefault();
                    modal.style.display = "flex";
                };
            }
            if (closeX && modal) {
                closeX.onclick = function() { modal.style.display = "none"; };
            }
            if (closeBtn && modal) {
                closeBtn.onclick = function() { modal.style.display = "none"; };
            }
            
            // 点击外部阴影关闭
            window.addEventListener("click", function(event) {
                if (modal && event.target === modal) {
                    modal.style.display = "none";
                }
            });
        };

        // 双保险：加载完或者正在加载，都能准确绑定
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", forceBindEvents);
        } else {
            forceBindEvents();
        }
    })();
</script>