#!/usr/bin/python

import sys
import os
from optparse import OptionParser
from datetime import datetime
import copy
import locale
import time

from odf import opendocument, text, draw, table
from odf.style import Style
from odf.style import TextProperties, TableCellProperties, ParagraphProperties
from odf.text import H, P, Span

from irg import IRG, get_param_monthly, get_image_info

def build_style(name, family, properties, attributes={}):
    style = Style(name=name, family=family, **attributes)
    for p in properties:
        style.addElement(p)
    return style

def replace_words(text, words):
    for a, b in words:
        text = text.replace(a, b)
    return text

def walk_replace(parent, words):
    for node in parent.childNodes:
        if node.nodeType == node.ELEMENT_NODE:
            for child in node.childNodes:
                if child.nodeType == node.TEXT_NODE:
                    child.data = replace_words(child.data, words)
            walk_replace(node, words)

def populate_words(year, month):
    timespan, start, end = get_param_monthly(year, month)
    start_time = time.localtime(start)
    end_time = time.localtime(end)

    en_month = time.strftime('%B', start_time)
    old = locale.getlocale(locale.LC_ALL)
    locale.setlocale(locale.LC_ALL, 'th_TH.UTF-8')

    words = [('%enmonth', en_month),
             ('%thmonth', time.strftime('%B', start_time).decode('utf-8')),
             ('%year', time.strftime('%Y', start_time)),
             ('%start_date', time.strftime('%d %B %Y', start_time).decode('utf-8')[1:]),
             ('%end_date', time.strftime('%d %B %Y', end_time).decode('utf-8')[1:])]

    locale.setlocale(locale.LC_ALL, old)
    return words

class Report:
    def __init__(self, irg, verbose=True):
        self.irg = irg
        self._verbose = verbose
        self.doc = opendocument.OpenDocumentText()

    def verbose(self, msg):
        if self._verbose:
            print msg

    def setup_styles(self):
        s = self.doc.styles
        a = self.doc.automaticstyles
        self.styles = {}
        self.auto_styles = {}

        self.styles['Standard'] = build_style(
                'Standard', 'paragraph', [
                ParagraphProperties(textalign='start',
                                    writingmode='lr-tb',
                                    orphans='2',
                                    widows='2'),
                TextProperties(fontsize='14pt',
                               fontfamily='Cordia New',
                               language='en',
                               country='US',
                               fontsizecomplex='14pt',
                               fontfamilycomplex='Cordia New',
                               languagecomplex='th',
                               countrycomplex='TH'),
        ], {'class': 'text'})
        s.addElement(self.styles['Standard'])

        self.styles['Text body'] = build_style(
                'Text body', 'paragraph', [
                ParagraphProperties(margintop='0in',
                                    marginbottom='0.0835in'),
                TextProperties(fontsize='14pt',
                               fontfamily='Cordia New',
                               language='en',
                               country='US',
                               fontsizecomplex='14pt',
                               fontfamilycomplex='Cordia New',
                               languagecomplex='th',
                               countrycomplex='TH'),
        ], {'parentstylename': 'Standard', 'class': 'text'})
        s.addElement(self.styles['Text body'])

        self.styles['First line indent'] = build_style(
                'First line indent', 'paragraph', [
                ParagraphProperties(textindent='0.5in',
                                    autotextindent='false'),
                TextProperties(fontsize='14pt',
                               fontfamily='Cordia New',
                               language='en',
                               country='US',
                               fontsizecomplex='14pt',
                               fontfamilycomplex='Cordia New',
                               languagecomplex='th',
                               countrycomplex='TH'),
        ], {'parentstylename': 'Text_20_body', 'class': 'text'})
        s.addElement(self.styles['First line indent'])

        self.styles['Heading 1'] = build_style(
                'Heading 1', 'paragraph', [
                TextProperties(fontsize='24pt',
                               fontfamily='Cordia New',
                               fontweight='bold',
                               language='en',
                               country='US',
                               fontsizecomplex='24pt',
                               fontfamilycomplex='Cordia New',
                               fontweightcomplex='bold',
                               languagecomplex='th',
                               countrycomplex='TH'),
                ParagraphProperties(keepwithnext='always',
                                    breakbefore='page'),
        ])
        s.addElement(self.styles['Heading 1'])

        self.styles['Heading 2'] = build_style(
                'Heading 2', 'paragraph', [
                TextProperties(fontsize='20pt',
                               fontfamily='Cordia New',
                               fontweight='bold',
                               language='en',
                               country='US',
                               fontsizecomplex='20pt',
                               fontfamilycomplex='Cordia New',
                               fontweightcomplex='bold',
                               languagecomplex='th',
                               countrycomplex='TH'),
                ParagraphProperties(keepwithnext='always'),
        ])
        s.addElement(self.styles['Heading 2'])

        self.styles['Heading 3'] = build_style(
                'Heading 3', 'paragraph', [
                TextProperties(fontsize='18pt',
                               fontfamily='Cordia New',
                               fontweight='bold',
                               language='en',
                               country='US',
                               fontsizecomplex='18pt',
                               fontfamilycomplex='Cordia New',
                               fontweightcomplex='bold',
                               languagecomplex='th',
                               countrycomplex='TH'),
                ParagraphProperties(keepwithnext='always'),
        ])
        s.addElement(self.styles['Heading 3'])

        self.auto_styles['Table.HC'] = build_style(
                'Table.HC', 'table-cell', [
                TableCellProperties(verticalalign='middle',
                                    backgroundcolor='#777777'),
        ])
        a.addElement(self.auto_styles['Table.HC'])

        self.styles['Table Heading'] = build_style(
                'Table Heading', 'paragraph', [
                TextProperties(fontsize='14pt',
                               fontfamily='Cordia New',
                               fontweight='bold',
                               language='en',
                               country='US',
                               fontsizecomplex='14pt',
                               fontfamilycomplex='Cordia New',
                               fontweightcomplex='bold',
                               languagecomplex='th',
                               countrycomplex='TH'),
                ParagraphProperties(textalign='center'),
        ])
        s.addElement(self.styles['Table Heading'])

        self.auto_styles['Table.DC'] = build_style(
                'Table.DC', 'paragraph', [
                TextProperties(fontsize='14pt',
                               fontfamily='Cordia New',
                               language='en',
                               country='US',
                               fontsizecomplex='14pt',
                               fontfamilycomplex='Cordia New',
                               languagecomplex='th',
                               countrycomplex='TH'),
                ParagraphProperties(textalign='center'),
        ])
        a.addElement(self.auto_styles['Table.DC'])

        self.auto_styles['Table.DL'] = build_style(
                'Table.DL', 'paragraph', [
                TextProperties(fontsize='14pt',
                               fontfamily='Cordia New',
                               language='en',
                               country='US',
                               fontsizecomplex='14pt',
                               fontfamilycomplex='Cordia New',
                               languagecomplex='th',
                               countrycomplex='TH'),
                ParagraphProperties(textalign='left'),
        ])
        a.addElement(self.auto_styles['Table.DL'])

    def insert_doc(self, filename, keywords):
        doc = opendocument.load(filename)
        walk_replace(doc.text, keywords)

        s = self.doc.styles
        a = self.doc.automaticstyles

#        for e in doc.styles.childNodes:
#            print e.tagName, e.attributes
#            if e.tagName in ['style:style', 'text:style']:
#                s.addElement(copy.deepcopy(e))
#            else:
#                print e.tagName, e.attributes
        for e in doc.automaticstyles.childNodes:
#            print e.tagName, e.attributes
            if e.tagName in ['style:style', 'text:style']:
                a.addElement(copy.deepcopy(e))
#            else:
#                print e.tagName, e.attributes
        for e in doc.text.childNodes:
            if e.tagName not in ['office:forms', 'text:sequence-decls']:
                self.doc.text.addElement(copy.deepcopy(e))

#        for e in a.childNodes:
#            print e.tagName, e.attributes

    def generate_graph(self, stat, image):
        title = stat['meta']['title']
        image_name = 'graph_%s.png' % stat['meta']['graph_id']
        mime, width, height = get_image_info(image)
        width = width*72/96
        height = height*72/96

        h = H(outlinelevel=2, stylename=self.styles['Heading 2'], text=title)
        self.doc.text.addElement(h)

        p = P()
        self.doc.text.addElement(p)
        f = draw.Frame(width='%fpt' % width, height='%fpt' % height,
                       anchortype='as-char')
        p.addElement(f)

        href = self.doc.addPicture(image_name, content=image)
        im = draw.Image(href=href)
        f.addElement(im)

    def generate_stat(self, stat):
        headers = ['Value', 'Average', 'Peak', 'Prime Time Average',
                   'Previous Average', 'Previous Peak',
                   'Previous Prime time Average']
        columns = ['title', 'avg', 'max', 'p_avg',
                   'pre_avg', 'pre_max', 'pre_p_avg']
        tab = table.Table()
        self.doc.text.addElement(tab)

        t = table.TableHeaderColumns()
        t.addElement(table.TableColumn())
        tab.addElement(t)

        t = table.TableColumns()
        t.addElement(table.TableColumn(numbercolumnsrepeated=str(len(columns)-1)))
        tab.addElement(t)

        t = table.TableHeaderRows()
        tab.addElement(t)
        tr = table.TableRow()
        t.addElement(tr)
        for text in headers:
            tc = table.TableCell(valuetype='string', stylename=self.auto_styles['Table.HC'])
            tr.addElement(tc)
            tc.addElement(P(text=text, stylename=self.styles['Table Heading']))

        t = table.TableRows()
        tab.addElement(t)
        for row in stat['cols']:
            tr = table.TableRow()
            t.addElement(tr)
            first = True
            for c in columns:
                val = str(row[c]).strip()
                if first:
                    style = self.auto_styles['Table.DL']
                    first = False
                else:
                    style = self.auto_styles['Table.DC']
                tc = table.TableCell(valuetype='string', value=val)
                tr.addElement(tc)
                tc.addElement(P(text=val, stylename=style))

    def generate(self, year, month, report_name):
        report = self.irg.get_report_by_name(report_name)
        timespan, start, end = get_param_monthly(year, month)

        h = H(outlinelevel=1, stylename=self.styles['Heading 1'], text='Monthly Report')
        self.doc.text.addElement(h)

        for graph_id in report['graph_ids']:
            self.verbose('generating graph %s' % graph_id)
            stat = self.irg.get_stat(graph_id, report['rratype_id'],
                                     timespan, start, end,
                                     report['begin_prime'], report['end_prime'])
            image = self.irg.get_graph_image(graph_id, report['rratype_id'],
                                             start, end)
            self.generate_graph(stat, image)
            self.generate_stat(stat)

        p = P()
        self.doc.text.addElement(p)

    def save(self, filename):
        self.doc.save(filename)
        self.verbose('saved %s' % filename)

class App:
    def __init__(self):
        self.parse_args()

    def parse_args(self):
        now = datetime.now()
        usage = 'usage: %prog [options] cacti_url'
        parser = OptionParser(usage=usage)

        parser.add_option('-v', '--verbose', dest='verbose',
                          default=False, action='store_true',
                          help='Enable verbose mode [no]')
        parser.add_option('-u', '--user', dest='user',
                          help='Cacti username [required]')
        parser.add_option('-p', '--password', dest='password',
                          help='Cacti password [required]')
        parser.add_option('-y', '--year', dest='year', type='int',
                          default=now.year,
                          help='Year [%d]' % now.year)
        parser.add_option('-m', '--month', dest='month', type='int',
                          default=now.month,
                          help='Month [%d]' % now.month)
        parser.add_option('-r', '--report', dest='report',
                          default='report',
                          help='Report template name [report]')
        parser.add_option('--header', dest='header',
                          default='REPORT_header.odt',
                          help='Header filename with REPORT substitution [report_header.odt]')
        parser.add_option('--footer', dest='footer',
                          default='REPORT_footer.odt',
                          help='Footer filename with REPORT substitution [report_footer.odt]')
        parser.add_option('-o', '--output', dest='output',
                          default='REPORT_YYYYMM.odt',
                          help='Output filename with REPORT, YYYY, MM substitution [report_%d%d.odt]' % (now.year, now.month))

        self.options, self.args = parser.parse_args()

        if len(self.args) != 1 or \
           not self.options.user or not self.options.password:
            parser.print_help()
            sys.exit(-1)
        self.url = self.args[0]

        self.keywords = [('REPORT', str(self.options.report)),
                         ('YYYY', str(self.options.year)),
                         ('MM', str(self.options.month))]
        self.keywords += populate_words(self.options.year, self.options.month)

        self.options.output = replace_words(self.options.output, self.keywords)
        self.options.header = replace_words(self.options.header, self.keywords)
        self.options.footer = replace_words(self.options.footer, self.keywords)

    def run(self):
        irg = IRG(self.url, self.options.user, self.options.password,
                  verbose=self.options.verbose)
        report = Report(irg, verbose=self.options.verbose)
        if os.path.exists(self.options.header):
            report.insert_doc(self.options.header, self.keywords)
        report.setup_styles()
        report.generate(self.options.year, self.options.month,
                        self.options.report)
        if os.path.exists(self.options.footer):
            report.insert_doc(self.options.footer, self.keywords)
        report.save(self.options.output)

if __name__ == '__main__':
    app = App()
    app.run()
