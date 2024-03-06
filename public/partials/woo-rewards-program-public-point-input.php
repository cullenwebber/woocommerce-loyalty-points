<p class="form-row form-row-wide">
    <label for="used_points">Points to claim (<?php echo $points_redeemable; ?> redeemable out of <?php echo $user_points; ?>)<span class="required">*</span></label>
    <input type="range" class="input-range form-control" id="used_points" name="used_points" value="0" min="0" max="<?php echo $user_points; ?>" step="<?php echo $point_threshold; ?>" oninput="updateTextInput(this.value)" />
    <span id="pointsDisplay">0 / <?php echo $user_points; ?> points</span>
</p>

<script>
    function updateTextInput(value) {
        var roundedValue = Math.floor(value / <?php echo $point_threshold; ?>) * <?php echo $point_threshold; ?>;
        document.getElementById('pointsDisplay').innerText = roundedValue + ' / <?php echo $user_points; ?> points';
        document.querySelector('#points_used_field').value = roundedValue;
    }
</script>