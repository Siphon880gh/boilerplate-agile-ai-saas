from datetime import datetime, timezone, timedelta
import calendar

TIME_ZONE_CONTEXT = timedelta(hours=-8)

def get_current_time_hr():
    local_time = datetime.now(timezone(TIME_ZONE_CONTEXT))
    formatted_time = local_time.strftime("%Y-%m-%d %H:%M:%S")
    return formatted_time


def get_number_of_x_days_later_hr(days):
    local_time = datetime.now(timezone(TIME_ZONE_CONTEXT))
    x_days_later = local_time + timedelta(days=days)  # approximate 1 month as 30 days
    x_days_later = x_days_later.strftime("%Y-%m-%d %H:%M:%S")
    return x_days_later


def get_one_month_later_hr():
    local_time = datetime.now(timezone(TIME_ZONE_CONTEXT))
    
    # Extract current year and month
    year = local_time.year
    month = local_time.month
    
    # Determine the next month and year
    if month == 12:  # If December, wrap around to January of the next year
        next_month = 1
        next_year = year + 1
    else:
        next_month = month + 1
        next_year = year
    
    # Get the number of days in the next month
    days_in_next_month = calendar.monthrange(next_year, next_month)[1]
    
    # Adjust the day to ensure it doesn't exceed the maximum in the next month
    day = min(local_time.day, days_in_next_month)
    
    # Create the datetime object for one month later
    one_month_later = datetime(next_year, next_month, day, 
                               local_time.hour, local_time.minute, local_time.second, 
                               tzinfo=local_time.tzinfo)
    
    # Format the date into the desired string format
    formatted_time = one_month_later.strftime("%Y-%m-%d %H:%M:%S")
    return formatted_time