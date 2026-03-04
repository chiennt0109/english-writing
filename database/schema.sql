PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(20) NOT NULL CHECK(role IN ('student','teacher','admin')),
  created_at DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS topics (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  title VARCHAR(120) NOT NULL,
  description TEXT
);

CREATE TABLE IF NOT EXISTS tasks (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  topic_id INTEGER NOT NULL,
  title VARCHAR(150) NOT NULL,
  prompt TEXT NOT NULL,
  requirements_json TEXT NOT NULL,
  outline_json TEXT NOT NULL,
  min_words INTEGER NOT NULL,
  max_words INTEGER NOT NULL,
  level VARCHAR(5) NOT NULL,
  FOREIGN KEY (topic_id) REFERENCES topics(id)
);

CREATE TABLE IF NOT EXISTS submissions (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  task_id INTEGER NOT NULL,
  title VARCHAR(150) NOT NULL,
  content TEXT NOT NULL,
  word_count INTEGER NOT NULL,
  status VARCHAR(20) NOT NULL CHECK(status IN ('draft','submitted','reviewed')),
  created_at DATETIME NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (task_id) REFERENCES tasks(id)
);

CREATE TABLE IF NOT EXISTS submission_versions (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  submission_id INTEGER NOT NULL,
  version_no INTEGER NOT NULL,
  content TEXT NOT NULL,
  created_at DATETIME NOT NULL,
  FOREIGN KEY (submission_id) REFERENCES submissions(id)
);

CREATE TABLE IF NOT EXISTS auto_feedback (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  submission_id INTEGER NOT NULL,
  annotations_json TEXT NOT NULL,
  summary_json TEXT NOT NULL,
  error_counts_json TEXT NOT NULL,
  created_at DATETIME NOT NULL,
  FOREIGN KEY (submission_id) REFERENCES submissions(id)
);

CREATE TABLE IF NOT EXISTS teacher_reviews (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  submission_id INTEGER NOT NULL,
  teacher_id INTEGER NOT NULL,
  score_task REAL NOT NULL,
  score_coh REAL NOT NULL,
  score_lex REAL NOT NULL,
  score_gra REAL NOT NULL,
  overall REAL NOT NULL,
  comments TEXT,
  featured INTEGER DEFAULT 0,
  reviewed_at DATETIME NOT NULL,
  FOREIGN KEY (submission_id) REFERENCES submissions(id),
  FOREIGN KEY (teacher_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS featured_essays (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  submission_id INTEGER UNIQUE NOT NULL,
  approved_by INTEGER NOT NULL,
  approved_at DATETIME NOT NULL,
  FOREIGN KEY (submission_id) REFERENCES submissions(id),
  FOREIGN KEY (approved_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS error_events (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  submission_id INTEGER NOT NULL,
  error_code VARCHAR(40) NOT NULL,
  severity VARCHAR(20) NOT NULL,
  snippet TEXT,
  created_at DATETIME NOT NULL,
  FOREIGN KEY (submission_id) REFERENCES submissions(id)
);
