import psycopg2
from psycopg2 import sql
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

        cursor.close()
        conn.close()

    except Exception as e:
        print("Error:", e)

if __name__ == "__main__":
    main()
