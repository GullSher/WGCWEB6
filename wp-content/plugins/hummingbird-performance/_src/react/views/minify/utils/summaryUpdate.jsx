import { useEffect } from 'react';
import { dispatch } from '@wordpress/data';
import { STORE_NAME } from '../../../data/minify';

const useSummaryUpdate = () => {
  
  useEffect(() => {
    function reloadSummary() {
        dispatch( STORE_NAME ).invalidateResolution( 'getOptions' );
    }
  
    const frmElem  = document.getElementById("wphb-minification-tools-form"); 

    if( frmElem ) {
        // Listen for the event.
        document.getElementById("wphb-minification-tools-form").addEventListener(
            "reloadSummary",
            reloadSummary,
            false
        );
        return () => {
            document.getElementById("wphb-minification-tools-form").removeEventListener('reloadSummary', reloadSummary);
        };
    }
  }, []);
  return;
}
export default useSummaryUpdate;
