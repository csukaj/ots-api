class PriceError(Exception):
    def __init__(self, message):
        super(PriceError, self).__init__(message)
