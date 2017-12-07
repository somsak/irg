## irg (Inox Report Generator)
  This project provide tools for generate report from cacti and Zabbix

The project contain 2 components
  1. repoti - A cacti plugin used to generate report.
  2. irg-genreport - This program accept cacti URL on cacti server with repoti to generate a report in OpenOffice Document format.

## Installation

 1. This only tested on Linux (Ubuntu) and Mac. Don't sure if it will
    work on Windows.
 1. Install dependencies using your desired package management system or
    just 'pip install' it.
   1. odfpy
   1. pyzabbix
 1. Clone this repository somewhere.
 1. Set PYTHONPATH to the irg/binding/python directory

## How to generate report from Zabbix

 1. In Zabbix, create one or more Screen that will represent your report.
   1. The screen must only has 1 column and contains only Graph. Recommend width is 600 and height is 100.
   1. Name it with some nice name. The name will appear as Heading 1 in the generated report.
 1. Don't forget to set PYTHONPATH
 1. Run the report generation command './irg-genreport'. For example

```
./irg-genreport -v --type=zabbix -u report -p some_nice_password --range=20171101-20171201 -r "Cloud CPU Load Report,Cloud Memory Report,CPU Load Report,Traffic Report" --header path_to_header.odt -o output.odt https://your.domain/zabbix/
```
