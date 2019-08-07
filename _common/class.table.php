<?php
/*
*    Table Class
*
*    I never really liked the other Table classes out there.  They always
*    seemed more trouble than they were worth.  However, i could definetly see
*    the value in them.  This class is set to be as simple as possible, at the
*    same time giving you as many options as possible.  Essentially, you define
*    the attributes, the content, and where it all goes.  It also allows you to
*    define some default values so you don't have to rewrite certain attributes.
*    Of course, you can still override these values.
*
*    It should be noted that this is the order of precidence for the styles.
*        1. Default
*        2. Row
*        3. Column
*        4. Individually Assigned
*
*    This means that if you assign a default style for all cells, then assign
*    a column style, that column style will override the default cell style.
*
*    @subpackage     HTML Table Class
*    @package        Cereal Class Library
*    @copyright      Jason Lotito 2001
*    @version        1.0
*    @author         Jason Lotito
*    
*/

class Table
{
    /**
    * Table structure array
    *
    * This is the table array.  It keeps track of all cell content and attributes.
    * $table[0]["table_values"] is reserved for table information.  Cells start 
    * at $table[1][1].
    * 
    * @access    private
    */
    var $table;

    /**
    * Default settings array
    *
    * These are the default settings array.  All default settings are stored
    * here and are not moved to the $table array until the CompileTable method is
    * resolved, and even then, it is only moving it to a temporary array.
    * 
    * @access    private
    */
    var $default_settings;

    /**
    * Total rows array
    *
    * This keeps track of the total number of rows.
    * 
    * @access    private
    * @type        int
    */
    var $row_count;

    /**
    * Fancy styles array
    *
    * The fancy styles array.  This stores information regarding row and column
    * styles.  Anything added to make display of certain effects easier ( this
    * is to imply that any further display-like mehthods add will be stored in
    * this array here ).
    * 
    * @access    private
    */
    var $fstyles;

    /**
    * Initializes the class variables.
    *
    * @access    public
    */
    function Table ()
    {
        $this->table = array();
        $this->default_settings = array();
        $this->row_count = 0;
        $this->default_settings = array();
        $this->fstyles = array();
    }

    /**
    * Adds 1 to the row_count count, keeping track of the total
    * number of rows in the table.  It returns the current
    * row_count after the addition.
    *
    * @access    public
    * @return    int        Returns the current number of rows
    */
    function AddRow ()
    {
        return ++$this->row_count;
    }

    /**
    * Returns the current row you are working with.
    *
    * @access    public
    * @return    int        Returns the current number of rows.
    */
    function GetCurrentRow ()
    {
        return $this->row_count;
    }

    /**
    * Returns the current total number of columns in your table
    *
    * @access    public
    * @return    int        Returns the current number of rows.
    */
    function GetCurrentCols ()
    {
        $high = 0;
        foreach ( $this->table as $row => $array )
        {
            $cnt = count( $array );
            if ( $cnt > $high || $high == 0 )
            {
                $high = $cnt;
            }
        }
        return $high;
    }

    /**
    * Set the cell's content.
    *
    * Allows you to easily set the content of a cell.  The first
    * parameter required is the row the cell is on, followed by
    * the column the cell is in, and then finally, what the
    * content of the cell is.
    * 
    * @access    public
    * @param    int        $row
    * @param    int        $col
    * @param    string    $content
    */
    function SetCellContent ( $row, $col, $content )
    {
        $this->table[$row][$col]["content"] = $content;
    }

    function SetRowContent ( $row, $content )
    {
        $count = count($content);
        $content_keys = array_keys($content);
        for ( $x = 0; $x < $count; $x++ )
        {
            $this->table[$row][( $x + 1 )]["content"] = $content[$content_keys[$x]];
        }
    }

    function SetColContent ( $col, $content )
    {
        $count = count($content);
        $content_keys = array_keys($content);
        for ( $x = 0; $x < $count; $x++ )
        {
            $this->table[( $x + 1 )][$col]["content"] = $content[$content_keys[$x]];
        }
    }

    /**
    * Sets the default content for all cells.
    *
    * @access    public
    * @param    string $content
    */
    function SetDefaultCellContent ( $content )
    {
        $this->default_settings['td']['content'] = $content;
    }

    /**
    * Set the cells BBGCOLOR setting
    * Allows you to easily set the bgcolor of the cell.
    * This works the same as the SetCellContent method.
    *
    * @access    public
    * @param    int        $row
    * @param    int        $col
    * @param    string    $bgcolor
    */
    function SetCellBGColor ( $row, $col, $bgcolor )
    {
        $this->table[$row][$col]["bgcolor"] = $bgcolor;
    }

    /**
    * Sets the default bgcolor for all cells.
    *
    * @access    public
    * @param    string    $bgcolor
    */
    function SetDefaultBGColor ( $bgcolor )
    {
        $this->default_settings['td']['bgcolor'] = $bgcolor;
    }

    /**
    * Allows you to easily set the style of the cell using CSS.
    * This works the same as the SetCellContent method.
    *
    * @access    public
    * @param    int        $row
    * @param    int        $col
    * @param    string    $style
    */
    function SetCellStyle ( $row, $col, $style )
    {
        $this->table[$row][$col]["style"] = $style;
    }

    /**
    * Sets the default style for all cells.
    *
    * @access    public
    * @param    string    $style
    */
    function SetDefaultStyle ( $style )
    {
        $this->default_settings['td']['style'] = $style;
    }

    /**
    * Allows you to easily set the colspan attribute of the cell
    * if need be.  This works the same as the SetCellContent
    * method.
    *
    * @access    public
    * @param    int        $row
    * @param    int        $col
    * @param    int        $colspan
    */
    function SetCellColSpan ( $row, $col, $colspan )
    {
        $this->table[$row][$col]["colspan"] = $colspan;
    }

    /**
    * Allows you to easily set the rowspan attribute of the cell
    * if need be.  This works the same as the SetCellContent
    * method.
    *
    * @access    public
    * @param    int        $row
    * @param    int        $col
    * @param    int        $rowspan
    */
    function SetCellRowSpan ( $row, $col, $rowspan )
    {
        $this->table[$row][$col]["rowspan"] = $rowspan;
    }

    /**
    * Allows you to easily set any one attribute of the cell
    * if need be.  This works the same as the SetCellContent
    * method.
    *
    * @access    public
    * @param    int        $row
    * @param    int        $col
    * @param    string    $attribute
    * @param    string    $value
    * @return    void
    */
    function SetCellAttribute ( $row, $col, $attribute, $value )
    {
        $this->table[$row][$col][$attribute] = $value;
    }

    /**
    * Sets the default attribute for all cells.
    *
    * @access    public
    * @param    string    $attribute
    * @param    string    $value
    * @return    void
    */
    function SetDefaultCellAttribute ( $attribute, $value )
    {
        $this->default_settings['td'][$attribute] = $value;
    }

    /**
    * Allows you to set multiple attributes for a cell in one
    * method call.  You would call it like this:
    * $attributes = array ( "attribute_name" => "value",
    * "attribute_name_2" => "value");
    * $table->SetCellAttributes( $row, $col, $attributes );
    *
    * @access    public
    * @param    int        $row
    * @param    int        $col
    * @param    array    $array
    * @return    void
    */
    function SetCellAttributes ( $row, $col, $array )
    {
        if ( is_array($array) )
        {
            foreach ( $array as $attribute => $value )
            {
                $this->table[$row][$col][$attribute] = $value;
            }
        }
    }

    /**
    * Set td default attributes
    *
    * Same setup as SetCellAttributes, however, without the row or
    * col parameters (however, the array still works the same way)
    * 'type' is what you are setting default attributes for.
    * Default attributes can be overriden simply by using one of
    * the aboe methods.  If you specifically set an attribute
    * using the 'row', 'col', 'attribute' methods above, they will
    * override the Default Attribute for that one element.
    *
    * @access    public    
    * @param    array    $array
    */
    function SetDefaultCellAttributes ( $array )
    {
        foreach ( $array as $key => $value )
        {
            $this->default_settings["td"][$key] = $value;
        }
    }

    /**
    * Set table default attributes
    *
    * Same setup as SetCellAttributes, however, without the row or
    * col parameters (however, the array still works the same way)
    * 'type' is what you are setting default attributes for.
    * Default attributes can be overriden simply by using one of
    * the aboe methods.  If you specifically set an attribute
    * using the 'row', 'col', 'attribute' methods above, they will
    * override the Default Attribute for that one element.
    *
    * @access    public    
    * @param    array    $array
    */
    function SetDefaultTableAttribute ( $array )
    {
        foreach ( $array as $key => $value )
        {
            $this->default_settings["table"][$key] = $value;
        }
    }

    /**
    * Set table attributes
    *
    * Sets the table attributes in the same manner as the
    * SetCellAttributes array.  The table information is stored
    * in the table array as the first element ($this->table[0]).
    *
    * @access    public
    * @param    array    $array
    * @return    void
    */
    function SetTableAttributes ( $array )
    {
        foreach ( $array as $key => $value )
        {
            $this->table[0]["table_values"][$key] = $value;
        }
    }
    
    /**
    * Set table attribute
    *
    * Sets the table attributes in the same manner as the
    * SetCellAttributes array.  The table information is stored
    * in the table array as the first element ($this->table[0]).
    *
    * @access    public
    * @param    array    $array
    * @return    void
    */
    function SetTableAttribute ( $key, $value )
    {
        $this->table[0]["table_values"][$key] = $value;
    }

    /**
    * Set attributes to particular rows.
    *
    * You can easily setup attributes for an entire row using this method.  You
    * simply send it a row number (or an array of row numbers if more than one)
    * as well as the attributes in an array format (like we do every place else)
    * and all the cells in the row number will be set with those attributes.
    * Styles set in this manner can be overridden by
    *
    * @access    public
    * @param    int or array        $row
    * @param    array                $attributes
    */
    function SetFancyRowStyle ( $row, $attributes )
    {
        if ( is_array( $row ) )
        {
            foreach ( $row as $num )
            {
                foreach( $attributes as $key => $value )
                {
                    $this->fstyles["row"][$num][$key] = $value;
                }
            }
        } else {
            foreach( $attributes as $key => $value )
            {
                $this->fstyles["row"][$row][$key] = $value;
            }
        }
    }

    /**
    * Set attributes to particular column.
    *
    * This works the same way as the SetFancyRowStyle method, except that this
    * method works on arrays.  This method will override SetFancyRowStyle styles
    * which are set.
    *
    * @access    public
    * @param    int || array        $row
    * @param    array                $attributes
    */
    function SetFancyColStyle ( $col, $attributes )
    {
        if ( is_array( $col ) )
        {
            foreach ( $col as $num )
            {
                foreach( $attributes as $key => $value )
                {
                    $this->fstyles["col"][$num][$key] = $value;
                }
            }
        } else {
            foreach( $attributes as $key => $value )
            {
                $this->fstyles["col"][$col][$key] = $value;
            }
        }
    }

    /**
    * Set alternating colors.
    *
    * You simply put in two HTML compatible colors, like #ffffff, or 'white' and
    * it will creat alternating color rows.
    *
    * @access    public
    * @param    string    $odd_colors        The odd numbered rows bgcolor value
    * @param    string    $even_colors    The even numbered rows bgcolor value
    * @param    int        $start            What row to start outputting the alternating colors on. Defaults to 1 (the first row).
    * @param    int        $end            What row to stop outputting the alternating colors on.  Defaults to the GetCurrentRow() value
    *
    */
    function Set2RowColors( $odd_colors, $even_colors, $start=1, $end=false )
    {
        if( $end === false )
        {
            $end = $this->GetCurrentRow();
        }
        for( $row = $start; $row <= $end; $row++ )
        {
            if ( ( $row % 2 ) != 0 )
            {
                $this->fstyles["row"][$row]["bgcolor"] = $odd_colors;
            } else {
                $this->fstyles["row"][$row]["bgcolor"] = $even_colors;
            }
        }
    }

    /**
    * Compile table to HTML.
    *
    * Turns the table array into an HTML table.  This must be
    * called before a table can be printed out.
    *
    * @access    public
    * @return    string    Returns a string of the table in HTML Format
    */
    function CompileTable ()
    {
        $content = "\n<table";
        
        if ( isset( $this->default_settings["table"] ) )
        {
            $t_array = $this->default_settings["table"];
            foreach ( $t_array as $attribute => $value )
            {
                $this->table[0]["table_values"][$attribute] = $value;
            }
        }

        if ( isset( $this->table[0]["table_values"] ) )
        {
            $t_array = $this->table[0]["table_values"];
            foreach ( $t_array as $attribute => $value )
            {
                $content .= ' '.$attribute.'="'.$value.'"';
            }
        }

        $content .= ">\n";

        for ( $row = 1; $row <= $this->row_count; $row++ )
        {

            $content .= "\t<tr>\n";
            $row_array = $this->table[$row];
            
            /*
            $td_array = $this->default_settings["td"];
            $frowstyle = $this->fstyles["row"][$row];
            if ( is_array( $frowstyle ) )
            {
                foreach ( $frowstyle as $attribute => $value )
                {
                    $td_array[$attribute] = $value;
                }
            }*/

            $count = count( $row_array );
            for ( $col = 1; $col <= $count; $col++ )
            {
                $colvalue = array();
                $colvalue = $row_array[$col];
                $content .= "\t\t<td";
                $fcolstyle = "";

                $td_array = $this->default_settings["td"];
                if ( isset($this->fstyles["row"][$row]) )
                {
                    $frowstyle = $this->fstyles["row"][$row];
                } else {
                    $frowstyle = "";    
                }
                if ( is_array( $frowstyle ) )
                {
                    foreach ( $frowstyle as $attribute => $value )
                    {
                        $td_array[$attribute] = $value;
                    }
                }
                
                if ( isset( $this->fstyles["col"][$col] ) && !empty( $this->fstyles["col"][$col] ) )
                {
                    $fcolstyle = $this->fstyles["col"][$col];
                    if ( is_array( $fcolstyle ) )
                    {
                        foreach ( $fcolstyle as $attribute => $value )
                        {
                            $td_array[$attribute] = $value;
                        }
                    }
                }
                
                if ( is_array( $td_array ) )
                {
                    foreach ( $td_array as $attribute => $value )
                    {
                        if ( empty( $colvalue[$attribute] ) || !isset( $colvalue[$attribute] ) )
                        {
                            $colvalue[$attribute] = $value;
                        }
                    }
                }
                foreach ( $colvalue as $attribute => $value)
                {
                    if ( $attribute == "content" )
                    {
                        $t_string = $value;
                    } else {
                        $content .= " $attribute=\"$value\"";
                    }
                }
                $content .= ">\n";
                $content .= "\t\t\t".$t_string."\n";
                $content .= "\t\t</td>\n";
            }
            $content .= "\t</tr>\n";
        }
        $content .= "</table>\n";
        return $content;
    }

    /**
    * Prints out the compiled table.
    *
    * @access    public
    */
    function PrintTable ()
    {
        echo $this->CompileTable();
    }

    /**
    * Allows you to quickly see what is located and where it's
    * located in your table array.  Used for debugging purposes.
    *
    * @access    dev
    */
    function printTableArray()
    {
        echo "<pre>";
        print_r( $this );
        echo "</pre>";
    }
}



/*
*
*    DOCINFO
*        TABSIZE:            4 SPACES
*        TAB_OR_SPACE:        TAB
*        LANGUAGE:            PHP
*        EDITOR:            EditPlus
*/
?>