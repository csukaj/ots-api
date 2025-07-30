import pprint
import time


class Logger:
    def __init__(self):
        pass

    @staticmethod
    def log(out_file, message):
        target = open(out_file, "a")
        target.write(str(message) + "\n")
        target.close()

    @staticmethod
    def plog(out_file, message):
        pp = pprint.PrettyPrinter(indent=1, width=1)
        target = open(out_file, "a")
        target.write(pp.pformat(message))
        target.write("\n")
        target.close()

    @staticmethod
    def debug(message, pretty=True):
        if pretty:
            Logger.plog("/tmp/debug.log", message)
        else:
            Logger.log("/tmp/debug.log", message)


def time_usage(func):
    """
    use as decorator.
    eg:

    @time_usage
    def get_something(self):
        pass
    """

    def log_wrapper(*args, **kwargs):
        beg_ts = time.time()
        return_value = func(*args, **kwargs)
        end_ts = time.time()
        Logger.debug(str(round((end_ts - beg_ts) * 1000, 1)) + "ms @ function " + func.__name__)
        return return_value

    return log_wrapper
