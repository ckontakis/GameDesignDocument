<div id="node-popUp">
    <span id="nodeOperation">node</span>
    <br>
    <table style="margin:auto;">
        <tr>
            <td>id</td><td>
                <input id="node-id" disabled="true" value="new value" />
            </td>
        </tr>
        <tr>
            <td>label</td><td>
                <input id="node-label" value="new value" />
            </td>
        </tr>
        <tr>
            <td>Node type</td><td>
                <select id="node-type">
                    <option value="Start">Start</option>
                    <option value="Narrative">Narrative</option>
                    <option value="Goal">Goal</option>
                    <option value="Choice">Choice</option>
                    <option value="Good Ending">Good Ending</option>
                    <option value="Bad Ending">Bad Ending</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>Narrative Phase</td><td>
                <select id="narrative-phase">
                    <option value="Exposition">Exposition</option>
                    <option value="Rising Action">Rising Action</option>
                    <option value="Climax">Climax</option>
                    <option value="Falling Action">Falling Action</option>
                    <option value="Conclusion">Conclusion</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>Other Info:</td>
            <td>
                <textarea id="node-info" style="resize: none" cols="20" rows="2" value="" /></textarea>
            </td>
        </tr>
        <tr><td>Fixed Node:</td><td><input type="checkbox" id="nodeFix"  checked><br></td></tr>

    </table>
    <input type="button" value="save" id="nodeSaveButton" />
    <input type="button" value="cancel" id="nodeCancelButton" />
</div>


<div id="edge-popUp">
    <span id="edgeOperation">edge</span>
    <br>
    <table style="margin:auto;">
        <tr>
            <td>id</td><td>
                <input id="edge-id" disabled="true" value="new value"/>
            </td>
        </tr>
        <tr>
            <td>label</td><td>
                <input id="edge-label" value="" />
            </td>
        </tr>
    </table>
    <input type="button" value="save" id="edgeSaveButton" />
    <input type="button" value="cancel" id="edgeCancelButton" />
</div>
