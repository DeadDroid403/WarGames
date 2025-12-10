import psycopg2
from psycopg2 import sql
import bcrypt
from os import getenv

# Database connection settings
DB_HOST = "db"
DB_PORT = 5432
DB_NAME = getenv("POSTGRES_DB")
DB_USER = getenv("POSTGRES_USER")
DB_PASS = getenv("POSTGRES_PASSWORD")

create_table_query = """
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'player',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
"""

create_challenges_table = """
CREATE TABLE IF NOT EXISTS challenges (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    author VARCHAR(50) NOT NULL,
    description TEXT,
    category VARCHAR(50),
    difficulty VARCHAR(20),     -- e.g. Easy, Medium, Hard
    language VARCHAR(50),
    file_path VARCHAR(255),     -- Stores path like 'uploads/my_chall.zip'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
"""


# Admin credentials
ADMIN_USERNAME = "admin"
ADMIN_EMAIL = "admin@example.com"
ADMIN_PASSWORD = "admin"   # update this
ADMIN_ROLE = "admin"

def main():
    try:
        # connect to postgres
        conn = psycopg2.connect(
            host=DB_HOST,
            port=DB_PORT,
            dbname=DB_NAME,
            user=DB_USER,
            password=DB_PASS
        )
        conn.autocommit = True

        cursor = conn.cursor()
        cursor.execute(create_table_query)
        print("✓ users table created (or already exists).")

        cursor = conn.cursor()
        cursor.execute(create_challenges_table)
        print("✓ challenges table created (or already exists).")
        cursor.close()
        conn.close()

    except Exception as e:
        print("Error:", e)

def create_admin():
    try:
        # Hash password in PHP-compatible format
        hashed = bcrypt.hashpw(ADMIN_PASSWORD.encode(), bcrypt.gensalt())
        hashed = hashed.decode()  # convert bytes to string
        
        print("Generated bcrypt hash:", hashed)

        conn = psycopg2.connect(
            host=DB_HOST,
            port=DB_PORT,
            dbname=DB_NAME,
            user=DB_USER,
            password=DB_PASS
        )
        conn.autocommit = True
        cur = conn.cursor()

        # Create admin user
        insert_query = """
        INSERT INTO users (username, email, password, role)
        VALUES (%s, %s, %s, %s)
        ON CONFLICT (username) DO NOTHING;
        """
        
        cur.execute(insert_query, (ADMIN_USERNAME, ADMIN_EMAIL, hashed, ADMIN_ROLE))
        print("✓ Admin user created or already exists.")

        cur.close()
        conn.close()

    except Exception as e:
        print("Error:", e)

def create_challenge(name, author, description, category, difficulty, language, file_path):
    """Inserts a new challenge into the database."""
    try:
        conn = psycopg2.connect(
            host=DB_HOST,
            port=DB_PORT,
            dbname=DB_NAME,
            user=DB_USER,
            password=DB_PASS
        )
        conn.autocommit = True
        cur = conn.cursor()

        insert_challenge = """
        INSERT INTO challenges (name, author, description, category, difficulty, language, file_path)
        VALUES (%s, %s, %s, %s, %s, %s, %s)
        RETURNING id;
        """

        cur.execute(insert_challenge, (
            name,
            author,
            description,
            category,
            difficulty,
            language,
            file_path
        ))

        challenge_id = cur.fetchone()[0]
        print(f"✓ Challenge added successfully (ID: {challenge_id})")

        cur.close()
        conn.close()

    except Exception as e:
        print("Error adding challenge:", e)

if __name__ == "__main__":
    main()
    create_admin()
    create_challenge(
        name="Log Me In",
        author="Droid",
        description="Log in as Admin if You C4n !!!",
        category="web",
        difficulty="easy",
        language="python",
        file_path="challenges/logmein.zip"
    )
