# Booking Calendar
The booking calendar is based around importing and exporting bookings using .isc files. This gives good flexability with most calendar software. It also allows you to setup repeating rules (rrules) in your calendar where you may have repeating availability for say Friday evening to Monday morning.

## Commands
### Calendar Import
```larabook:calendar-import```Can be run manually to import a users holiday. You will need to provide the ics URL and the user ID.

The import will look 5 years into the future.

Username, password and useragent are currently not supported.

An option 'tag' such as [holiday] can be provided that will be searched for in the description. If not present, then the entry will not be imported.


## Scheduled Commands

### Import of schedule
The incoming calendar ICS URL can be added at ```https://elitebookingsystem.com/account#calendar-integration```

## Holiday / Days off

## Special Pricing of dates

## Exporting of the calendar
The calendar ICS file URL is available at ```https://elitebookingsystem.com/account#calendar-integration``` 
