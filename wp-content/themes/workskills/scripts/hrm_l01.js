<script type="text/javascript">
    var dialogueTimings = [1,40,44,50,52,89,92,106,113,114,143,145,162,165,176,179,192,194,213,215,233,235,244,259,262,268,270,288,290,303,306,338,369,371,393,413,425,445,447,488,490,511,524],
        dialogues = document.querySelectorAll('#transcript>div'),
        transcriptWrapper = document.querySelector('#transcriptWrapper'),
        audio = document.querySelector('#audio1'),
        previousDialogueTime = -1;   

     function playTranscript() {

        var currentDialogueTime = Math.max.apply(Math, dialogueTimings.filter(function(v){return v <= audio.currentTime}));

        if(previousDialogueTime !== currentDialogueTime) {
            previousDialogueTime = currentDialogueTime;
            var currentDialogue = dialogues[dialogueTimings.indexOf(currentDialogueTime)];
            transcriptWrapper.scrollTop  = currentDialogue.offsetTop - 50;  
            var previousDialogue = document.getElementsByClassName('speaking')[0];
            if(previousDialogue !== undefined)
                previousDialogue.className = previousDialogue.className.replace('speaking','');
            currentDialogue.className +=' speaking';
        }
    }
;
</script>