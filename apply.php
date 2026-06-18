<?php
require_once 'includes/db.php';
$pageTitle = 'Apply — TechAdmit';
$user = requireLogin();
if ($user['role'] === 'admin') { header('Location: admin/dashboard.php'); exit; }

$error = $success = '';
$courses = $conn->query("SELECT * FROM courses ORDER BY name");
$courseList = [];
while ($c = $courses->fetch_assoc()) $courseList[$c['id']] = $c;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- collect & validate ---
    $course_id    = (int)($_POST['course_id'] ?? 0);
    $first_name   = sanitize($conn, $_POST['first_name'] ?? '');
    $last_name    = sanitize($conn, $_POST['last_name'] ?? '');
    $dob          = sanitize($conn, $_POST['dob'] ?? '');
    $gender       = sanitize($conn, $_POST['gender'] ?? '');
    $mobile       = sanitize($conn, $_POST['mobile'] ?? '');
    $category     = sanitize($conn, $_POST['category'] ?? '');
    $address      = sanitize($conn, $_POST['address'] ?? '');
    $state        = sanitize($conn, $_POST['state'] ?? '');
    $pincode      = sanitize($conn, $_POST['pincode'] ?? '');
    $score_10     = (float)($_POST['score_10'] ?? 0);
    $score_12     = (float)($_POST['score_12'] ?? 0);
    $jee_pct      = (float)($_POST['jee_percentile'] ?? 0);
    $jee_rank     = (int)($_POST['jee_rank'] ?? 0);
    $cet_pct      = (float)($_POST['cet_percentile'] ?? 0);
    $board        = sanitize($conn, $_POST['board'] ?? '');
    $passing_year = (int)($_POST['passing_year'] ?? date('Y'));

    if (!$course_id || !$first_name || !$last_name || !$dob || !$gender || !$mobile || !$category || !$address || !$state || !$pincode) {
        $error = 'Please fill in all required fields.';
    } elseif ($score_12 < 60) {
        $error = 'Minimum 60% in Class 12 is required for eligibility.';
    } else {
        // generate unique app number
        $app_number = 'TA' . date('Y') . strtoupper(substr(md5(uniqid()), 0, 6));
        $uid = $user['id'];

        $stmt = $conn->prepare(
            "INSERT INTO applications
             (app_number, user_id, course_id, first_name, last_name, dob, gender, mobile, category,
              address, state, pincode, score_10, score_12, jee_percentile, jee_rank, cet_percentile,
              board, passing_year)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
        );
        $stmt->bind_param(
            'siisssssssssddddisi',
            $app_number, $uid, $course_id, $first_name, $last_name, $dob, $gender, $mobile,
            $category, $address, $state, $pincode, $score_10, $score_12, $jee_pct, $jee_rank,
            $cet_pct, $board, $passing_year
        );
        if ($stmt->execute()) {
            header('Location: my_applications.php?submitted=' . $app_number);
            exit;
        } else {
            $error = 'Submission failed: ' . $conn->error;
        }
    }
}

include 'includes/header.php';
?>

<div class="container" style="max-width:780px;">
  <div class="page-title">Apply for B.Tech Admission</div>
  <div class="page-sub">Fill all 3 sections and submit your application.</div>

  <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>

  <!-- Steps bar -->
  <div class="steps-bar">
    <div class="step-tab active"><span class="snum">01</span>Course</div>
    <div class="step-tab"><span class="snum">02</span>Personal Info</div>
    <div class="step-tab"><span class="snum">03</span>Academics</div>
  </div>

  <form method="POST">

    <!-- PANEL 1: Course -->
    <div class="form-panel card" style="margin-bottom:1rem;">
      <div class="form-group">
        <label>Select Programme *</label>
        <select name="course_id" required>
          <option value="">— Choose a course —</option>
          <?php foreach ($courseList as $id => $c): ?>
            <option value="<?= $id ?>" <?= (($_POST['course_id'] ?? '') == $id) ? 'selected' : '' ?>>
              <?= htmlspecialchars($c['name']) ?> (<?= $c['seats'] ?> seats | ₹<?= number_format($c['fee_per_yr']) ?>/yr)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="display:flex;justify-content:flex-end;">
        <button type="button" class="btn btn-primary btn-next">Next: Personal Info →</button>
      </div>
    </div>

    <!-- PANEL 2: Personal Info -->
    <div class="form-panel card" style="margin-bottom:1rem;display:none;">
      <div class="form-grid-2">
        <div class="form-group">
          <label>First Name *</label>
          <input type="text" name="first_name" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" placeholder="Akshat" required>
        </div>
        <div class="form-group">
          <label>Last Name *</label>
          <input type="text" name="last_name" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" placeholder="Khanna" required>
        </div>
        <div class="form-group">
          <label>Date of Birth *</label>
          <input type="date" name="dob" value="<?= htmlspecialchars($_POST['dob'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label>Gender *</label>
          <select name="gender" required>
            <option value="">Select</option>
            <?php foreach (['Male','Female','Non-binary','Prefer not to say'] as $g): ?>
              <option <?= (($_POST['gender'] ?? '') === $g) ? 'selected' : '' ?>><?= $g ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Mobile Number *</label>
          <input type="tel" name="mobile" value="<?= htmlspecialchars($_POST['mobile'] ?? '') ?>" placeholder="+91 98765 43210" required>
        </div>
        <div class="form-group">
          <label>Category *</label>
          <select name="category" required>
            <option value="">Select</option>
            <?php foreach (['General','OBC','SC','ST','EWS'] as $cat): ?>
              <option <?= (($_POST['category'] ?? '') === $cat) ? 'selected' : '' ?>><?= $cat ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group form-full">
          <label>Permanent Address *</label>
          <textarea name="address" placeholder="House No., Street, City" required><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label>State *</label>
          <select name="state" required>
            <option value="">Select state</option>
            <?php foreach (['Andhra Pradesh','Bihar','Delhi','Gujarat','Haryana','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Punjab','Rajasthan','Tamil Nadu','Uttar Pradesh','West Bengal','Other'] as $s): ?>
              <option <?= (($_POST['state'] ?? '') === $s) ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>PIN Code *</label>
          <input type="text" name="pincode" value="<?= htmlspecialchars($_POST['pincode'] ?? '') ?>" placeholder="110001" maxlength="6" required>
        </div>
      </div>
      <div style="display:flex;justify-content:space-between;margin-top:0.5rem;">
        <button type="button" class="btn btn-outline btn-prev" style="color:var(--ink);border-color:var(--line);">← Back</button>
        <button type="button" class="btn btn-primary btn-next">Next: Academics →</button>
      </div>
    </div>

    <!-- PANEL 3: Academics -->
    <div class="form-panel card" style="display:none;">
      <div class="form-grid-2">
        <div class="form-group">
          <label>Class 10 Score (%) *</label>
          <input type="number" name="score_10" min="0" max="100" step="0.01" value="<?= $_POST['score_10'] ?? '' ?>" placeholder="92.5" required>
        </div>
        <div class="form-group">
          <label>Class 12 PCM (%) *</label>
          <input type="number" name="score_12" min="0" max="100" step="0.01" value="<?= $_POST['score_12'] ?? '' ?>" placeholder="88.0" required>
        </div>
        <div class="form-group">
          <label>JEE Main Percentile *</label>
          <input type="number" name="jee_percentile" min="0" max="100" step="0.01" value="<?= $_POST['jee_percentile'] ?? '' ?>" placeholder="95.40" required>
        </div>
        <div class="form-group">
          <label>JEE Advanced Rank <small>(optional)</small></label>
          <input type="number" name="jee_rank" min="0" value="<?= $_POST['jee_rank'] ?? '' ?>" placeholder="5230">
        </div>
        <div class="form-group">
          <label>State CET Percentile <small>(if applicable)</small></label>
          <input type="number" name="cet_percentile" min="0" max="100" step="0.01" value="<?= $_POST['cet_percentile'] ?? '' ?>" placeholder="97.2">
        </div>
        <div class="form-group">
          <label>Board (Class 12) *</label>
          <select name="board" required>
            <?php foreach (['CBSE','ICSE','State Board','IB','Other'] as $b): ?>
              <option <?= (($_POST['board'] ?? '') === $b) ? 'selected' : '' ?>><?= $b ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Year of Passing (12th) *</label>
          <select name="passing_year" required>
            <?php foreach ([2025, 2024, 2023, 2022] as $yr): ?>
              <option <?= (($_POST['passing_year'] ?? '') == $yr) ? 'selected' : '' ?>><?= $yr ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="alert alert-info" style="margin-top:0.5rem;">
        By submitting, I confirm all information is accurate. False information may lead to cancellation of admission.
      </div>
      <div style="display:flex;justify-content:space-between;margin-top:1rem;">
        <button type="button" class="btn btn-outline btn-prev" style="color:var(--ink);border-color:var(--line);">← Back</button>
        <button type="submit" class="btn btn-primary">Submit Application ✓</button>
      </div>
    </div>

  </form>
</div>

<?php include 'includes/footer.php'; ?>
