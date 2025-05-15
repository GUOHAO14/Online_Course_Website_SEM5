<!DOCTYPE html>
<style>
    .sidebar {
        height: 100vh;
        width: 220px;
        position: fixed;
        top: 0;
        left: 0;
        background-color: #333;
        padding-top: 20px;
        color: white;
        z-index: 1000;
    }

    .sidebar a {
        padding: 12px 16px;
        display: block;
        color: white;
        text-decoration: none;
        transition: background 0.3s;
    }

    .sidebar a:hover {
        background-color: #575757;
    }

    .sidebar h2 {
        text-align: center;
        margin-bottom: 20px;
        font-size: 20px;
        color: #ddd;
    }

    .main-content {
        margin-left: 220px;
        padding: 20px;
    }
</style>

<div style="line-height: normal;" class="sidebar">
    <h2>Dashboard</h2>
    <a href="admin_proposal.php">Course Approval</a>
    <a href="admin_course.php">Courses</a>
    <a href="admin_lecturer.php">Lecturers</a>
    <a href="admin_student.php">Students</a>
    <a href="admin_banlist.php">Ban List</a>
    <a href="admin_career_field.php">Careers and Field</a>
    <a href="admin_report.php">Reports</a>
    <a href="admin_FAQ.php">FAQ Management</a>
    <a href="admin_discussion.php">Discussion Forum</a>
    <a href="admin_usersup.php">User Support</a>
    <a href="admin_profile.php">Account</a>
</div>
