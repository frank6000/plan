-- =========================================================
-- ระบบติดตามโครงการ กิจกรรม งบประมาณ และ KPI
-- สร้างจาก plan.md — MariaDB 10.11 / InnoDB / utf8mb4
-- =========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =========================================================
-- 1) ตารางหลัก (Master / Lookup) — ไม่มี FK ออกไปที่อื่น
-- =========================================================

CREATE TABLE fiscal_years (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  year_be SMALLINT UNSIGNED NOT NULL,
  label VARCHAR(100) NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_fiscal_years_year_be (year_be)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE departments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(30) NULL,
  name VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_departments_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE plans (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(30) NULL,
  name VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_plans_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE strategic_issues (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(500) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE moph_excellence (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(20) NULL,
  name VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE health_networks (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE budget_sources (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(30) NULL,
  name VARCHAR(150) NOT NULL,
  category ENUM('NON_UC','เงินบำรุง','อื่นๆ') NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE months_ref (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  month_no TINYINT UNSIGNED NOT NULL,        -- 1=ม.ค. .. 12=ธ.ค. (ปฏิทินสากล)
  month_name VARCHAR(20) NOT NULL,
  fiscal_order TINYINT UNSIGNED NOT NULL,    -- 1=ต.ค. .. 12=ก.ย. (ลำดับปีงบประมาณไทย)
  quarter TINYINT UNSIGNED NOT NULL,         -- 1-4 ตามปีงบประมาณ
  UNIQUE KEY uq_months_ref_month_no (month_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE material_catalog (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(30) NULL,
  name VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE priority_levels (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(30) NULL,
  name VARCHAR(255) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- สถานะโครงการ (15 ขั้น) — self-FK ไปยังแถวอื่นในตารางเดียวกัน
CREATE TABLE project_status (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(10) NOT NULL,
  name VARCHAR(255) NOT NULL,
  sort_order INT NOT NULL,
  next_status_id INT UNSIGNED NULL,
  return_status_id INT UNSIGNED NULL,
  UNIQUE KEY uq_project_status_code (code),
  CONSTRAINT fk_project_status_next FOREIGN KEY (next_status_id) REFERENCES project_status(id) ON DELETE SET NULL,
  CONSTRAINT fk_project_status_return FOREIGN KEY (return_status_id) REFERENCES project_status(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- สถานะกิจกรรม (3 ขั้น) — โครงสร้างเดียวกับ project_status แต่คนละ workflow
CREATE TABLE activity_status (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(10) NOT NULL,
  name VARCHAR(255) NOT NULL,
  sort_order INT NOT NULL,
  next_status_id INT UNSIGNED NULL,
  return_status_id INT UNSIGNED NULL,
  UNIQUE KEY uq_activity_status_code (code),
  CONSTRAINT fk_activity_status_next FOREIGN KEY (next_status_id) REFERENCES activity_status(id) ON DELETE SET NULL,
  CONSTRAINT fk_activity_status_return FOREIGN KEY (return_status_id) REFERENCES activity_status(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 2) ตารางหลักระดับ 2 (มี FK ไปตารางหลักระดับ 1)
-- =========================================================

CREATE TABLE main_projects (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(20) NULL,
  name VARCHAR(500) NOT NULL,
  plan_id INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_main_projects_code (code),
  KEY idx_main_projects_plan (plan_id),
  CONSTRAINT fk_main_projects_plan FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE health_facilities (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(20) NULL,
  name VARCHAR(255) NOT NULL,
  facility_type ENUM('รพ.สต.','ศสช.','รพ.','สสอ.','อื่นๆ') NOT NULL,
  network_id INT UNSIGNED NULL,
  tambon VARCHAR(100) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_health_facilities_code (code),
  KEY idx_health_facilities_network (network_id),
  CONSTRAINT fk_health_facilities_network FOREIGN KEY (network_id) REFERENCES health_networks(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE kpi_master (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(20) NULL,
  name VARCHAR(500) NOT NULL,
  main_project_id INT UNSIGNED NULL,
  result_level ENUM('output','outcome','impact') NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_kpi_master_code (code),
  KEY idx_kpi_master_main_project (main_project_id),
  FULLTEXT KEY ft_kpi_master_name (name),
  CONSTRAINT fk_kpi_master_main_project FOREIGN KEY (main_project_id) REFERENCES main_projects(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE project_templates (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  template_name VARCHAR(255) NOT NULL,
  department_id INT UNSIGNED NULL,
  objective TEXT NULL,
  strategic_issue_id INT UNSIGNED NULL,
  goal TEXT NULL,
  kpi_text TEXT NULL,
  target_text TEXT NULL,
  strategy_text TEXT NULL,
  suggested_budget_note VARCHAR(500) NULL,
  duration_text VARCHAR(255) NULL,
  responsible_person VARCHAR(255) NULL,
  expected_outcome TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_project_templates_department (department_id),
  KEY idx_project_templates_strategic_issue (strategic_issue_id),
  CONSTRAINT fk_project_templates_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
  CONSTRAINT fk_project_templates_strategic_issue FOREIGN KEY (strategic_issue_id) REFERENCES strategic_issues(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 3) ผู้ใช้งาน
-- =========================================================

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  auth_provider ENUM('google','line','local') NOT NULL,
  provider_user_id VARCHAR(191) NULL,
  username VARCHAR(100) NULL,
  password_hash VARCHAR(255) NULL,
  email VARCHAR(255) NULL,
  name VARCHAR(255) NOT NULL,
  avatar_url VARCHAR(500) NULL,
  level ENUM('หน่วยบริการ','สสอ.','รพ.','สสจ.','admin') NULL,
  status ENUM('Pending','Approve','Suspended') NOT NULL DEFAULT 'Pending',
  office_id INT UNSIGNED NULL,
  facility_id INT UNSIGNED NULL,
  must_change_password TINYINT(1) NOT NULL DEFAULT 0,
  failed_login_count INT UNSIGNED NOT NULL DEFAULT 0,
  locked_until DATETIME NULL,
  email_verified_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_users_provider (auth_provider, provider_user_id),
  UNIQUE KEY uq_users_username (username),
  UNIQUE KEY uq_users_email (email),
  KEY idx_users_office (office_id),
  KEY idx_users_facility (facility_id),
  CONSTRAINT fk_users_office FOREIGN KEY (office_id) REFERENCES departments(id) ON DELETE SET NULL,
  CONSTRAINT fk_users_facility FOREIGN KEY (facility_id) REFERENCES health_facilities(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 4) โครงการ (ตารางธุรกรรมหลัก)
-- =========================================================

CREATE TABLE projects (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_code VARCHAR(50) NOT NULL,
  sub_project_name VARCHAR(500) NOT NULL,
  fiscal_year_id INT UNSIGNED NOT NULL,
  network_id INT UNSIGNED NULL,
  plan_id INT UNSIGNED NULL,
  main_project_id INT UNSIGNED NULL,
  strategic_issue_id INT UNSIGNED NULL,
  moph_excellence_id INT UNSIGNED NULL,
  project_type ENUM('general','material_procurement') NOT NULL DEFAULT 'general',
  owner_office_id INT UNSIGNED NULL,
  owner_facility_id INT UNSIGNED NULL,
  planned_start_date DATE NULL,
  planned_end_date DATE NULL,
  budget_approved DECIMAL(14,2) NOT NULL DEFAULT 0,
  situation_analysis TEXT NOT NULL,
  situation_data_source VARCHAR(500) NOT NULL DEFAULT '',
  parent_project_id INT UNSIGNED NULL,
  status_id INT UNSIGNED NOT NULL,
  created_by INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_projects_code (project_code),
  KEY idx_projects_fiscal_year (fiscal_year_id),
  KEY idx_projects_owner_office (owner_office_id),
  KEY idx_projects_owner_facility (owner_facility_id),
  KEY idx_projects_status (status_id),
  KEY idx_projects_main_project (main_project_id),
  KEY idx_projects_parent (parent_project_id),
  FULLTEXT KEY ft_projects_name (sub_project_name),
  CONSTRAINT fk_projects_fiscal_year FOREIGN KEY (fiscal_year_id) REFERENCES fiscal_years(id) ON DELETE RESTRICT,
  CONSTRAINT fk_projects_network FOREIGN KEY (network_id) REFERENCES health_networks(id) ON DELETE SET NULL,
  CONSTRAINT fk_projects_plan FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE SET NULL,
  CONSTRAINT fk_projects_main_project FOREIGN KEY (main_project_id) REFERENCES main_projects(id) ON DELETE SET NULL,
  CONSTRAINT fk_projects_strategic_issue FOREIGN KEY (strategic_issue_id) REFERENCES strategic_issues(id) ON DELETE SET NULL,
  CONSTRAINT fk_projects_moph_excellence FOREIGN KEY (moph_excellence_id) REFERENCES moph_excellence(id) ON DELETE SET NULL,
  CONSTRAINT fk_projects_owner_office FOREIGN KEY (owner_office_id) REFERENCES departments(id) ON DELETE RESTRICT,
  CONSTRAINT fk_projects_owner_facility FOREIGN KEY (owner_facility_id) REFERENCES health_facilities(id) ON DELETE RESTRICT,
  CONSTRAINT fk_projects_status FOREIGN KEY (status_id) REFERENCES project_status(id) ON DELETE RESTRICT,
  CONSTRAINT fk_projects_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
  CONSTRAINT fk_projects_parent FOREIGN KEY (parent_project_id) REFERENCES projects(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE project_tracking_log (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  status_id INT UNSIGNED NOT NULL,
  tracking_date DATE NOT NULL,
  remark TEXT NULL,
  pm_name VARCHAR(255) NULL,
  recorded_by INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_ptl_project_date (project_id, tracking_date),
  CONSTRAINT fk_ptl_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT fk_ptl_status FOREIGN KEY (status_id) REFERENCES project_status(id) ON DELETE RESTRICT,
  CONSTRAINT fk_ptl_recorded_by FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE project_priority_scores (
  project_id INT UNSIGNED NOT NULL PRIMARY KEY,
  problem_size_score DECIMAL(4,1) NOT NULL,
  problem_severity_score DECIMAL(4,1) NOT NULL,
  intervention_effectiveness_score DECIMAL(3,2) NOT NULL,
  pearl_propriety TINYINT(1) NOT NULL DEFAULT 1,
  pearl_economics TINYINT(1) NOT NULL DEFAULT 1,
  pearl_acceptability TINYINT(1) NOT NULL DEFAULT 1,
  pearl_resources TINYINT(1) NOT NULL DEFAULT 1,
  pearl_legality TINYINT(1) NOT NULL DEFAULT 1,
  computed_bpr_score DECIMAL(10,2) NULL,
  scored_by INT UNSIGNED NOT NULL,
  scored_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pps_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT fk_pps_scored_by FOREIGN KEY (scored_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE project_priority_levels (
  project_id INT UNSIGNED NOT NULL,
  priority_level_id INT UNSIGNED NOT NULL,
  note VARCHAR(500) NULL,
  PRIMARY KEY (project_id, priority_level_id),
  KEY idx_ppl_priority_level (priority_level_id),
  CONSTRAINT fk_ppl_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT fk_ppl_priority_level FOREIGN KEY (priority_level_id) REFERENCES priority_levels(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE project_facilities (
  project_id INT UNSIGNED NOT NULL,
  facility_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (project_id, facility_id),
  KEY idx_pf_facility (facility_id),
  CONSTRAINT fk_pf_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT fk_pf_facility FOREIGN KEY (facility_id) REFERENCES health_facilities(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE project_materials (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  material_id INT UNSIGNED NULL,
  item_name VARCHAR(255) NOT NULL,
  quantity DECIMAL(10,2) NOT NULL,
  unit_price DECIMAL(14,2) NOT NULL,
  total_price DECIMAL(16,2) GENERATED ALWAYS AS (quantity * unit_price) STORED,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_pm_project (project_id),
  KEY idx_pm_material (material_id),
  CONSTRAINT fk_pm_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT fk_pm_material FOREIGN KEY (material_id) REFERENCES material_catalog(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 5) กิจกรรม
-- =========================================================

CREATE TABLE activities (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  activity_name TEXT NOT NULL,
  objective TEXT NULL,
  kpi_target_text TEXT NULL,
  target_area VARCHAR(500) NULL,
  target_group VARCHAR(1000) NULL,
  prevention_level ENUM('primary','secondary','tertiary') NULL,
  budget_plan DECIMAL(14,2) NOT NULL DEFAULT 0,
  budget_source_id INT UNSIGNED NULL,
  responsible_person VARCHAR(255) NULL,
  status_id INT UNSIGNED NOT NULL,
  created_by INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_activities_project (project_id),
  KEY idx_activities_status (status_id),
  KEY idx_activities_budget_source (budget_source_id),
  FULLTEXT KEY ft_activities_name (activity_name),
  CONSTRAINT fk_activities_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT fk_activities_budget_source FOREIGN KEY (budget_source_id) REFERENCES budget_sources(id) ON DELETE RESTRICT,
  CONSTRAINT fk_activities_status FOREIGN KEY (status_id) REFERENCES activity_status(id) ON DELETE RESTRICT,
  CONSTRAINT fk_activities_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activity_tracking_log (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  activity_id INT UNSIGNED NOT NULL,
  status_id INT UNSIGNED NOT NULL,
  tracking_date DATE NOT NULL,
  remark TEXT NULL,
  recorded_by INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_atl_activity_date (activity_id, tracking_date),
  CONSTRAINT fk_atl_activity FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
  CONSTRAINT fk_atl_status FOREIGN KEY (status_id) REFERENCES activity_status(id) ON DELETE RESTRICT,
  CONSTRAINT fk_atl_recorded_by FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activity_months (
  activity_id INT UNSIGNED NOT NULL,
  month_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (activity_id, month_id),
  KEY idx_am_month (month_id),
  CONSTRAINT fk_am_activity FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
  CONSTRAINT fk_am_month FOREIGN KEY (month_id) REFERENCES months_ref(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activity_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  activity_id INT UNSIGNED NOT NULL,
  log_date DATE NOT NULL,
  progress_percent TINYINT UNSIGNED NOT NULL DEFAULT 0,
  note TEXT NULL,
  recorded_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_al_activity (activity_id),
  CONSTRAINT fk_al_activity FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
  CONSTRAINT fk_al_recorded_by FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activity_facilities (
  activity_id INT UNSIGNED NOT NULL,
  facility_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (activity_id, facility_id),
  KEY idx_af_facility (facility_id),
  CONSTRAINT fk_af_activity FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
  CONSTRAINT fk_af_facility FOREIGN KEY (facility_id) REFERENCES health_facilities(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE budget_disbursements (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  activity_id INT UNSIGNED NOT NULL,
  project_id INT UNSIGNED NOT NULL,
  amount DECIMAL(14,2) NOT NULL,
  disbursement_date DATE NOT NULL,
  recorded_by INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_bd_activity (activity_id),
  KEY idx_bd_project (project_id),
  KEY idx_bd_date (disbursement_date),
  CONSTRAINT fk_bd_activity FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE RESTRICT,
  CONSTRAINT fk_bd_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE RESTRICT,
  CONSTRAINT fk_bd_recorded_by FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE RESTRICT,
  CONSTRAINT chk_bd_amount_positive CHECK (amount > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 6) KPI แบบต่อเนื่อง (project_kpi / kpi_progress)
-- =========================================================

CREATE TABLE project_kpi (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  kpi_id INT UNSIGNED NOT NULL,
  target_value DECIMAL(14,2) NULL,
  target_unit VARCHAR(100) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_project_kpi (project_id, kpi_id),
  KEY idx_project_kpi_kpi (kpi_id),
  CONSTRAINT fk_pk_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT fk_pk_kpi FOREIGN KEY (kpi_id) REFERENCES kpi_master(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE kpi_progress (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_kpi_id INT UNSIGNED NOT NULL,
  record_date DATE NOT NULL,
  actual_value DECIMAL(14,2) NOT NULL,
  recorded_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_kp_project_kpi (project_kpi_id),
  CONSTRAINT fk_kp_project_kpi FOREIGN KEY (project_kpi_id) REFERENCES project_kpi(id) ON DELETE CASCADE,
  CONSTRAINT fk_kp_recorded_by FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 7) OKR (objectives / key_results / key_result_progress / kpi_references)
-- =========================================================

CREATE TABLE objectives (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  kpi_id INT UNSIGNED NOT NULL,
  title VARCHAR(500) NOT NULL,
  fiscal_year_id INT UNSIGNED NOT NULL,
  quarter TINYINT UNSIGNED NULL,
  status ENUM('active','closed') NOT NULL DEFAULT 'active',
  final_score DECIMAL(5,2) NULL,
  created_by INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_obj_project (project_id),
  KEY idx_obj_kpi (kpi_id),
  KEY idx_obj_fiscal_year (fiscal_year_id),
  CONSTRAINT fk_obj_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT fk_obj_kpi FOREIGN KEY (kpi_id) REFERENCES kpi_master(id) ON DELETE RESTRICT,
  CONSTRAINT fk_obj_fiscal_year FOREIGN KEY (fiscal_year_id) REFERENCES fiscal_years(id) ON DELETE RESTRICT,
  CONSTRAINT fk_obj_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE key_results (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  objective_id INT UNSIGNED NOT NULL,
  description TEXT NOT NULL,
  baseline_value DECIMAL(14,2) NULL,
  target_value DECIMAL(14,2) NOT NULL,
  target_direction ENUM('increase','decrease') NOT NULL,
  unit VARCHAR(100) NULL,
  result_level ENUM('output','outcome','impact') NULL,
  final_score DECIMAL(5,2) NULL,
  created_by INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_kr_objective (objective_id),
  CONSTRAINT fk_kr_objective FOREIGN KEY (objective_id) REFERENCES objectives(id) ON DELETE CASCADE,
  CONSTRAINT fk_kr_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE key_result_progress (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  key_result_id INT UNSIGNED NOT NULL,
  record_date DATE NOT NULL,
  actual_value DECIMAL(14,2) NOT NULL,
  note TEXT NULL,
  recorded_by INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_krp_key_result (key_result_id),
  CONSTRAINT fk_krp_key_result FOREIGN KEY (key_result_id) REFERENCES key_results(id) ON DELETE CASCADE,
  CONSTRAINT fk_krp_recorded_by FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE kpi_references (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  key_result_id INT UNSIGNED NOT NULL,
  url VARCHAR(1000) NOT NULL,
  label VARCHAR(255) NULL,
  added_by INT UNSIGNED NOT NULL,
  added_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_kr_ref_key_result (key_result_id),
  CONSTRAINT fk_kr_ref_key_result FOREIGN KEY (key_result_id) REFERENCES key_results(id) ON DELETE CASCADE,
  CONSTRAINT fk_kr_ref_added_by FOREIGN KEY (added_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 8) แจ้งเตือน / บัญชีผู้ใช้ / ไฟล์แนบ / audit trail
-- =========================================================

CREATE TABLE notification_log (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  type ENUM('status_change','deadline_reminder') NOT NULL,
  sent_to VARCHAR(255) NOT NULL,
  sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_nl_project (project_id),
  CONSTRAINT fk_nl_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_resets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  token VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_password_resets_token (token),
  KEY idx_password_resets_user (user_id),
  CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE email_verifications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  token VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_email_verifications_token (token),
  KEY idx_email_verifications_user (user_id),
  CONSTRAINT fk_email_verifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE attachments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  related_table ENUM('projects','activities','budget_disbursements') NOT NULL,
  related_id INT UNSIGNED NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(500) NOT NULL,
  uploaded_by INT UNSIGNED NOT NULL,
  uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_attachments_related (related_table, related_id),
  CONSTRAINT fk_attachments_uploaded_by FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- audit_log: จงใจไม่ตั้ง FK ของ record_id ไปยังตารางต้นทาง (ตามข้อ 15.1 — ต้องอยู่รอดแม้ข้อมูลต้นทางถูกลบ)
CREATE TABLE audit_log (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  table_name VARCHAR(100) NOT NULL,
  record_id INT UNSIGNED NOT NULL,
  action ENUM('create','update','delete') NOT NULL,
  changed_by INT UNSIGNED NULL,
  changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ip_address VARCHAR(45) NULL,
  KEY idx_audit_log_table_record (table_name, record_id),
  KEY idx_audit_log_changed_at (changed_at),
  CONSTRAINT fk_audit_log_changed_by FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_log_details (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  audit_log_id INT UNSIGNED NOT NULL,
  field_name VARCHAR(100) NOT NULL,
  old_value TEXT NULL,
  new_value TEXT NULL,
  KEY idx_audit_log_details_log (audit_log_id),
  CONSTRAINT fk_audit_log_details_log FOREIGN KEY (audit_log_id) REFERENCES audit_log(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
