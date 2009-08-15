<?php
chdir('../../');

require("include/auth.php");
require("irg_cacti_api.php");

include("./include/top_graph_header.php");
include("menu.php");

$api = IRG::getInstance();
$hosts = $api->getCactiHost();

?>
<div style="width: 800px;">

    <style type="text/css" media="screen">
        h1 {
            color: red;
        }
    </style>

<h1><font>&lt;customer&gt;</font> - &lt;(daily|weekly|monthly|yearly)&gt; performance report</h1>
from <strong>yyyy-mm-dd to yyyy-mm-dd</strong>

<p>In writing, a report is a document characterized by information or other content reflective of inquiry or investigation, which is tailored to the context of a given situation and audience. The purpose of reports is usually to inform. However, reports may include persuasive elements, such as recommendations, suggestions, or other motivating conclusions that indicate possible future actions the report reader might take. Reports can be public or private, and often address questions posed by individuals in government, business, education, and science.[1] Reports often take the structure of scientific investigation: Introduction, Methods, Results and Discussion (IMRAD). They may sometimes follow a problem-solution structure based on the audience's questions or concerns. As for format, reports range from a simpler format with headings to indicate topics, to more complex formats including charts, tables, figures, pictures, tables of contents, abstracts, summaries, appendices, footnotes, hyperlinks, and references.</p>

<h3>Servers</h3>
<ul>
<?php foreach ($hosts as $host): ?>
    <li><?php  echo $host['hostname']?></li>
<?php endforeach ?>
</ul>

<?php foreach ($hosts as $host): ?>
    <h2><?php echo $host['id'] . " - " . $host['description'] . " - " . $host['hostname']; ?></h2>
    <h3>server description</h3>
    <p>Description is one of four rhetorical modes (also known as modes of discourse), along with exposition, argumentation, and narration. Each of the rhetorical modes is present in a variety of forms and each has its own purpose and conventions.</p>
    <p>Description is also the fiction-writing mode for transmitting a mental image of the particulars of a story.</p>
    <table border="1">      
        <th>graph</th><th>value</th><th>value</th>
        <tr><td>graph name</td><td>2</td><td>3</td></tr>
        <tr><td>graph name</td><td>5</td><td>6</td></tr>
        <tr><td>graph name</td><td>8</td><td>9</td></tr>
    </table>
    <br>
    <?php $graphs = $api->getCactiGraph($host['id']); ?>
    <?php foreach ($graphs as $graph): ?>
        <img src="/cacti/graph_image.php?local_graph_id=<?php echo $graph['id']; ?>&rra_id=3" />
        <div>
        <h3>Summary</h3>
        <p>A summary or recap is a shortened version of the original. The main purpose of such a simplification is to highlight the major points from the genuine (much longer) subject, e.g. a text, a film or an event. The target is to help the audience get the gist in a short period of time.</p>
        </div>
        <h3>note</h3>
        <ul>
            <li>note......</li>
        </ul>
    <?php endforeach ?>
    <hr>
<?php endforeach ?>
<div>
<h3>Report Summary</h3>
<p>A summary or recap is a shortened version of the original. The main purpose of such a simplification is to highlight the major points from the genuine (much longer) subject, e.g. a text, a film or an event. The target is to help the audience get the gist in a short period of time.</p>
</div></div>