class ObjectMapperError(Exception):
    def __init__(self, message):
        super(ObjectMapperError, self).__init__(message)
