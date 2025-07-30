import psycopg2
import psycopg2.extras


class PlpyMocker:
    def __init__(self):
        self.connection = None
        self.cursor = None
        pass

    def connect(self, host, database, user, password):
        self.connection = psycopg2.connect(host=host, database=database, user=user, password=password)
        self.cursor = self.connection.cursor(cursor_factory=psycopg2.extras.RealDictCursor)

    def disconnect(self):
        if self.connection:
            self.connection.close()

    def execute(self, query):
        self.cursor.execute(query)
        return self.cursor.fetchall()
